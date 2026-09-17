<?php

namespace App\Http\Controllers;

use App\Enums\PaymentType;
use App\Models\Contractor;
use App\Models\ContractorPayment;
use App\Models\Project;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ContractorPaymentController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();
        $query = ContractorPayment::query()->with(['project', 'contractor', 'recordedBy'])->where('is_voided', false);

        if ($user->isOwner()) {
            $projectIds = Project::where('owner_id', $user->id)->pluck('id');
            $query->whereIn('project_id', $projectIds);
        } elseif ($user->isContractor()) {
            $contractor = $user->contractorProfile;
            if ($contractor) {
                $query->where('contractor_id', $contractor->id);
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('contractor_id')) {
            $query->where('contractor_id', $request->contractor_id);
        }

        if ($request->filled('payment_type')) {
            $query->where('payment_type', $request->payment_type);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('payment_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('payment_date', '<=', $request->to_date);
        }

        $totalPaid = (float) (clone $query)->sum('amount');
        $payments = $query->latest('payment_date')->paginate(15)->withQueryString();

        $projects = $user->isOwner()
            ? Project::where('owner_id', $user->id)->get()
            : Project::all();

        $contractors = Contractor::where('is_active', true)->get();
        $paymentTypes = PaymentType::cases();

        return view('payments.index', compact('payments', 'totalPaid', 'projects', 'contractors', 'paymentTypes'));
    }

    public function create(Request $request): View
    {
        $user = auth()->user();
        $selectedProjectId = $request->query('project_id');
        $selectedContractorId = $request->query('contractor_id');

        $projects = $user->isOwner()
            ? Project::where('owner_id', $user->id)->with('contractors')->get()
            : Project::with('contractors')->get();

        $contractors = Contractor::where('is_active', true)->get();
        $paymentTypes = PaymentType::cases();

        return view('payments.create', compact('projects', 'contractors', 'paymentTypes', 'selectedProjectId', 'selectedContractorId'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'project_id' => ['required', 'exists:projects,id'],
            'contractor_id' => ['required', 'exists:contractors,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_date' => ['required', 'date'],
            'payment_type' => ['required', Rule::enum(PaymentType::class)],
            'reference' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $payment = ContractorPayment::create([
            'project_id' => $validated['project_id'],
            'contractor_id' => $validated['contractor_id'],
            'recorded_by' => auth()->id(),
            'amount' => $validated['amount'],
            'payment_date' => $validated['payment_date'],
            'payment_type' => $validated['payment_type'],
            'reference' => $validated['reference'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'is_voided' => false,
        ]);

        $contractor = Contractor::find($validated['contractor_id']);
        $project = Project::find($validated['project_id']);

        ActivityLogger::log([
            'project_id' => $project->id,
            'event' => 'contractor_payment_made',
            'description' => 'Paid PKR '.number_format($payment->amount)." to {$contractor->name} for project '{$project->name}' ({$payment->payment_type->label()})",
            'properties' => [
                'payment_id' => $payment->id,
                'contractor_id' => $contractor->id,
                'amount' => $payment->amount,
                'payment_type' => $payment->payment_type->value,
            ],
        ], $payment);

        return redirect()->route('contractor-payments.show', $payment)
            ->with('success', 'Payment voucher #'.str_pad($payment->id, 5, '0', STR_PAD_LEFT).' recorded successfully.');
    }

    public function show(ContractorPayment $contractorPayment): View
    {
        $contractorPayment->load(['project.owner', 'contractor', 'recordedBy']);

        // Contractor running stats on this project
        $contractAmount = (float) $contractorPayment->project
            ->contractors()
            ->where('contractors.id', $contractorPayment->contractor_id)
            ->first()?->pivot?->contract_amount ?? 0;

        $totalPaidToDate = (float) ContractorPayment::where('project_id', $contractorPayment->project_id)
            ->where('contractor_id', $contractorPayment->contractor_id)
            ->where('is_voided', false)
            ->where('payment_date', '<=', $contractorPayment->payment_date)
            ->sum('amount');

        $remainingBalance = max(0, $contractAmount - $totalPaidToDate);

        return view('payments.show', [
            'payment' => $contractorPayment,
            'contractAmount' => $contractAmount,
            'totalPaidToDate' => $totalPaidToDate,
            'remainingBalance' => $remainingBalance,
        ]);
    }

    public function destroy(ContractorPayment $contractorPayment): RedirectResponse
    {
        $contractorPayment->update(['is_voided' => true]);

        ActivityLogger::log([
            'project_id' => $contractorPayment->project_id,
            'event' => 'contractor_payment_voided',
            'description' => 'Voided payment voucher #'.str_pad($contractorPayment->id, 5, '0', STR_PAD_LEFT).' of PKR '.number_format($contractorPayment->amount),
            'properties' => ['payment_id' => $contractorPayment->id, 'amount' => $contractorPayment->amount],
        ], $contractorPayment);

        return redirect()->route('contractor-payments.index')
            ->with('success', 'Payment voucher #'.str_pad($contractorPayment->id, 5, '0', STR_PAD_LEFT).' marked as voided.');
    }
}
