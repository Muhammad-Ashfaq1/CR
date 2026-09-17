<?php

namespace App\Http\Controllers;

use App\Enums\PaymentType;
use App\Models\Contractor;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContractorController extends Controller
{
    public function index(Request $request): View
    {
        $query = Contractor::query()->withCount(['projects', 'workers', 'payments']);

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('cnic', 'like', "%{$search}%");
            });
        }

        $contractors = $query->latest()->paginate(15)->withQueryString();
        $users = User::where('role', 'contractor')
            ->whereDoesntHave('contractorProfile')
            ->get();

        return view('contractors.index', compact('contractors', 'users'));
    }

    public function create(): View
    {
        $users = User::where('role', 'contractor')
            ->whereDoesntHave('contractorProfile')
            ->get();

        return view('contractors.create', compact('users'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => ['nullable', 'exists:users,id'],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'cnic' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $contractor = Contractor::create([
            'user_id' => $validated['user_id'] ?? null,
            'name' => $validated['name'],
            'phone' => $validated['phone'] ?? null,
            'company_name' => $validated['company_name'] ?? null,
            'cnic' => $validated['cnic'] ?? null,
            'address' => $validated['address'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        ActivityLogger::log([
            'event' => 'contractor_created',
            'description' => "Created contractor profile for {$contractor->name}",
            'properties' => ['contractor_id' => $contractor->id],
        ], $contractor);

        return redirect()->route('contractors.show', $contractor)
            ->with('success', "Contractor '{$contractor->name}' created successfully.");
    }

    public function show(Contractor $contractor): View
    {
        $contractor->load([
            'user',
            'projects',
            'workers' => fn ($q) => $q->latest()->take(10),
            'payments.project',
        ]);

        // Calculate ledger
        $projectLedgers = $contractor->projects->map(function ($project) use ($contractor) {
            $contractAmount = (float) $project->pivot->contract_amount;
            $totalPaid = $contractor->totalPaidForProject($project->id);
            $remaining = $contractAmount - $totalPaid;

            return [
                'project' => $project,
                'contract_amount' => $contractAmount,
                'total_paid' => $totalPaid,
                'remaining' => $remaining,
                'is_primary' => $project->pivot->is_primary,
                'assigned_date' => $project->pivot->assigned_date,
            ];
        });

        $totalContractSum = $projectLedgers->sum('contract_amount');
        $totalPaidSum = $projectLedgers->sum('total_paid');
        $totalRemainingSum = $totalContractSum - $totalPaidSum;
        $paymentTypes = PaymentType::cases();
        $workerTypes = ['Mason', 'Laborer', 'Carpenter', 'Electrician', 'Plumber', 'Steel Fixer', 'Painter', 'Welder', 'Tile Fixer', 'Supervisor', 'Other'];

        return view('contractors.show', compact(
            'contractor',
            'projectLedgers',
            'totalContractSum',
            'totalPaidSum',
            'totalRemainingSum',
            'paymentTypes',
            'workerTypes'
        ));
    }

    public function edit(Contractor $contractor): View
    {
        $users = User::where('role', 'contractor')
            ->where(function ($q) use ($contractor) {
                $q->whereDoesntHave('contractorProfile')
                    ->orWhere('id', $contractor->user_id);
            })
            ->get();

        return view('contractors.edit', compact('contractor', 'users'));
    }

    public function update(Request $request, Contractor $contractor): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => ['nullable', 'exists:users,id'],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'cnic' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $contractor->update([
            'user_id' => $validated['user_id'] ?? null,
            'name' => $validated['name'],
            'phone' => $validated['phone'] ?? null,
            'company_name' => $validated['company_name'] ?? null,
            'cnic' => $validated['cnic'] ?? null,
            'address' => $validated['address'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        ActivityLogger::log([
            'event' => 'contractor_updated',
            'description' => "Updated contractor {$contractor->name}",
            'properties' => ['contractor_id' => $contractor->id],
        ], $contractor);

        return redirect()->route('contractors.show', $contractor)
            ->with('success', "Contractor '{$contractor->name}' updated successfully.");
    }

    public function destroy(Contractor $contractor): RedirectResponse
    {
        if ($contractor->projects()->exists() || $contractor->payments()->exists()) {
            return redirect()->route('contractors.index')
                ->with('error', "Cannot delete contractor '{$contractor->name}' because they have assigned projects or payment history.");
        }

        $name = $contractor->name;
        $contractor->delete();

        ActivityLogger::log([
            'event' => 'contractor_deleted',
            'description' => "Deleted contractor {$name}",
        ]);

        return redirect()->route('contractors.index')
            ->with('success', "Contractor '{$name}' deleted successfully.");
    }
}
