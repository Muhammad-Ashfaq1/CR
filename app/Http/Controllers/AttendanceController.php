<?php

namespace App\Http\Controllers;

use App\Enums\AttendanceStatus;
use App\Enums\PaymentType;
use App\Models\Attendance;
use App\Models\Contractor;
use App\Models\ContractorPayment;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Project;
use App\Models\Worker;
use App\Services\ActivityLogger;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    /**
     * Calculate start and end date for quick presets (e.g. Sat–Thu work week).
     */
    public static function calculatePresetDates(?string $preset): array
    {
        $now = now();

        if ($preset === 'this_work_week') {
            // Saturday to Thursday of current work cycle
            $start = ($now->dayOfWeek === Carbon::SATURDAY)
                ? $now->copy()->startOfDay()
                : $now->copy()->previous(Carbon::SATURDAY)->startOfDay();
            $end = $start->copy()->addDays(5)->endOfDay();

            return [$start->toDateString(), $end->toDateString()];
        }

        if ($preset === 'last_work_week') {
            $thisSat = ($now->dayOfWeek === Carbon::SATURDAY)
                ? $now->copy()->startOfDay()
                : $now->copy()->previous(Carbon::SATURDAY)->startOfDay();
            $start = $thisSat->subWeek();
            $end = $start->copy()->addDays(5)->endOfDay();

            return [$start->toDateString(), $end->toDateString()];
        }

        if ($preset === 'this_month') {
            return [$now->copy()->startOfMonth()->toDateString(), $now->copy()->endOfMonth()->toDateString()];
        }

        if ($preset === 'last_month') {
            return [$now->copy()->subMonth()->startOfMonth()->toDateString(), $now->copy()->subMonth()->endOfMonth()->toDateString()];
        }

        return [null, null];
    }

    /**
     * Daily attendance grid sheet for recording attendance.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $date = $request->query('date', now()->toDateString());
        $carbonDate = Carbon::parse($date);
        $selectedProjectId = $request->query('project_id');

        $projectsQuery = Project::query();
        if ($user->isOwner()) {
            $projectsQuery->where('owner_id', $user->id);
        } elseif ($user->isContractor()) {
            $contractor = $user->contractorProfile;
            if ($contractor) {
                $projectsQuery->whereHas('contractors', fn ($q) => $q->where('contractors.id', $contractor->id));
            } else {
                $projectsQuery->whereRaw('1 = 0');
            }
        }
        $projects = $projectsQuery->get();

        if (! $selectedProjectId && $projects->isNotEmpty()) {
            $selectedProjectId = $projects->first()->id;
        }

        $workers = collect();
        $existingAttendance = collect();

        if ($selectedProjectId) {
            $workersQuery = Worker::where('status', 'active')
                ->where(function ($q) use ($selectedProjectId) {
                    $q->where('project_id', $selectedProjectId)
                        ->orWhereNull('project_id');
                })
                ->with('contractor');

            if ($user->isContractor() && $user->contractorProfile) {
                $workersQuery->where('contractor_id', $user->contractorProfile->id);
            }

            $workers = $workersQuery->orderBy('name')->get();

            $existingAttendance = Attendance::where('project_id', $selectedProjectId)
                ->whereDate('attendance_date', $date)
                ->get()
                ->keyBy('worker_id');
        }

        $statuses = AttendanceStatus::cases();

        return view('attendance.index', compact(
            'date',
            'carbonDate',
            'projects',
            'selectedProjectId',
            'workers',
            'existingAttendance',
            'statuses'
        ));
    }

    /**
     * Batch store / update daily attendance.
     */
    public function storeDaily(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'project_id' => ['required', 'exists:projects,id'],
            'attendance_date' => ['required', 'date'],
            'attendance' => ['required', 'array'],
            'attendance.*.status' => ['required', 'in:full_day,half_day,absent,leave'],
            'attendance.*.notes' => ['nullable', 'string'],
        ]);

        $projectId = $validated['project_id'];
        $date = Carbon::parse($validated['attendance_date']);
        $recordedCount = 0;
        $totalPayable = 0.0;

        foreach ($validated['attendance'] as $workerId => $data) {
            $worker = Worker::find($workerId);
            if (! $worker) {
                continue;
            }

            $status = AttendanceStatus::from($data['status']);
            $wageAtTime = $worker->wageForDate($date);
            $payableAmount = Attendance::calculatePayable($status, $wageAtTime);

            Attendance::updateOrCreate(
                [
                    'worker_id' => $workerId,
                    'attendance_date' => $date->startOfDay(),
                ],
                [
                    'project_id' => $projectId,
                    'recorded_by' => auth()->id(),
                    'status' => $status,
                    'wage_at_time' => $wageAtTime,
                    'payable_amount' => $payableAmount,
                    'notes' => $data['notes'] ?? null,
                ]
            );

            $recordedCount++;
            $totalPayable += $payableAmount;
        }

        $project = Project::find($projectId);

        ActivityLogger::log([
            'project_id' => $projectId,
            'event' => 'attendance_recorded',
            'description' => "Recorded attendance for {$recordedCount} workers on {$date->format('d M Y')} (Total Payable: PKR ".number_format($totalPayable).')',
            'properties' => [
                'project_id' => $projectId,
                'date' => $date->toDateString(),
                'worker_count' => $recordedCount,
                'total_payable' => $totalPayable,
            ],
        ], $project);

        return redirect()->route('attendance.index', ['project_id' => $projectId, 'date' => $date->toDateString()])
            ->with('success', "Attendance saved for {$recordedCount} workers. Total payable: PKR ".number_format($totalPayable, 2));
    }

    /**
     * Attendance history, wage collection and ledger view.
     */
    public function history(Request $request): View
    {
        $user = auth()->user();
        $query = Attendance::query()->with(['worker.contractor', 'project', 'recordedBy']);

        if ($user->isOwner()) {
            $projectIds = Project::where('owner_id', $user->id)->pluck('id');
            $query->whereIn('project_id', $projectIds);
        } elseif ($user->isContractor()) {
            $contractor = $user->contractorProfile;
            if ($contractor) {
                $query->whereHas('worker', fn ($q) => $q->where('contractor_id', $contractor->id));
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('worker_id')) {
            $query->where('worker_id', $request->worker_id);
        }

        if ($request->filled('contractor_id')) {
            $query->whereHas('worker', fn ($q) => $q->where('contractor_id', $request->contractor_id));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Date presets & custom range
        $preset = $request->query('preset');
        $fromDate = $request->query('from_date');
        $toDate = $request->query('to_date');

        if ($preset) {
            [$presetFrom, $presetTo] = self::calculatePresetDates($preset);
            if ($presetFrom && $presetTo) {
                $fromDate = $presetFrom;
                $toDate = $presetTo;
            }
        }

        if ($fromDate) {
            $query->whereDate('attendance_date', '>=', $fromDate);
        }

        if ($toDate) {
            $query->whereDate('attendance_date', '<=', $toDate);
        }

        // Payment status filter
        $paymentStatus = $request->query('payment_status', 'all');
        if ($paymentStatus === 'unpaid') {
            $query->where('is_paid', false);
        } elseif ($paymentStatus === 'paid') {
            $query->where('is_paid', true);
        }

        $summaryQuery = clone $query;
        $totalWages = (float) (clone $summaryQuery)->sum('payable_amount');
        $unpaidWages = (float) (clone $summaryQuery)->where('is_paid', false)->sum('payable_amount');
        $paidWages = (float) (clone $summaryQuery)->where('is_paid', true)->sum('payable_amount');
        $unpaidRecordsCount = (clone $summaryQuery)->where('is_paid', false)->where('payable_amount', '>', 0)->count();

        $fullDaysCount = (clone $query)->where('status', AttendanceStatus::FullDay)->count();
        $halfDaysCount = (clone $query)->where('status', AttendanceStatus::HalfDay)->count();
        $absentCount = (clone $query)->where('status', AttendanceStatus::Absent)->count();

        // Worker-level wage collection breakdown for the filtered period
        $workerCollection = (clone $summaryQuery)
            ->selectRaw('worker_id,
                SUM(CASE WHEN status = "full_day" THEN 1 ELSE 0 END) as full_days,
                SUM(CASE WHEN status = "half_day" THEN 1 ELSE 0 END) as half_days,
                SUM(CASE WHEN status = "absent" THEN 1 ELSE 0 END) as absent_days,
                COUNT(*) as total_shifts,
                SUM(payable_amount) as total_payable,
                SUM(CASE WHEN is_paid = 1 THEN payable_amount ELSE 0 END) as paid_amount,
                SUM(CASE WHEN is_paid = 0 THEN payable_amount ELSE 0 END) as unpaid_amount')
            ->groupBy('worker_id')
            ->with(['worker.contractor', 'worker.project'])
            ->get();

        $records = $query->latest('attendance_date')->paginate(20)->withQueryString();

        $projects = $user->isOwner()
            ? Project::where('owner_id', $user->id)->get()
            : Project::all();

        $contractors = Contractor::where('is_active', true)->get();
        $workers = Worker::where('status', 'active')->get();
        $statuses = AttendanceStatus::cases();

        return view('attendance.history', compact(
            'records',
            'projects',
            'contractors',
            'workers',
            'statuses',
            'totalWages',
            'unpaidWages',
            'paidWages',
            'unpaidRecordsCount',
            'fullDaysCount',
            'halfDaysCount',
            'absentCount',
            'workerCollection',
            'preset',
            'fromDate',
            'toDate',
            'paymentStatus'
        ));
    }

    /**
     * Batch payout for unpaid attendance wages.
     */
    public function payAll(Request $request): RedirectResponse
    {
        $user = auth()->user();
        if (! $user->isOwner() && ! $user->isAdmin()) {
            abort(403, 'Only project owners and administrators can disburse wages.');
        }

        $validated = $request->validate([
            'project_id' => ['required', 'exists:projects,id'],
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date'],
            'worker_id' => ['nullable', 'exists:workers,id'],
            'contractor_id' => ['nullable', 'exists:contractors,id'],
            'payment_destination' => ['required', 'in:direct_pay,contractor_pay'],
            'payment_method' => ['required', 'string', 'in:cash,bank_transfer,cheque,online'],
            'payment_date' => ['required', 'date'],
            'reference' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $project = Project::findOrFail($validated['project_id']);
        if ($user->isOwner() && $project->owner_id !== $user->id) {
            abort(403, 'Unauthorized access to this project.');
        }

        $query = Attendance::where('project_id', $project->id)
            ->where('is_paid', false)
            ->where('payable_amount', '>', 0)
            ->with('worker.contractor');

        if (! empty($validated['from_date'])) {
            $query->whereDate('attendance_date', '>=', $validated['from_date']);
        }
        if (! empty($validated['to_date'])) {
            $query->whereDate('attendance_date', '<=', $validated['to_date']);
        }
        if (! empty($validated['worker_id'])) {
            $query->where('worker_id', $validated['worker_id']);
        }
        if (! empty($validated['contractor_id'])) {
            $query->whereHas('worker', fn ($q) => $q->where('contractor_id', $validated['contractor_id']));
        }

        $unpaidRecords = $query->get();

        if ($unpaidRecords->isEmpty()) {
            return redirect()->back()->with('error', 'No unpaid attendance wages found matching your selection.');
        }

        $totalAmount = (float) $unpaidRecords->sum('payable_amount');
        $shiftCount = $unpaidRecords->count();
        $uniqueWorkerCount = $unpaidRecords->pluck('worker_id')->unique()->count();
        $dateLabel = (! empty($validated['from_date']) ? Carbon::parse($validated['from_date'])->format('d M') : 'Start').' to '.(! empty($validated['to_date']) ? Carbon::parse($validated['to_date'])->format('d M Y') : 'End');
        $paymentRef = ! empty($validated['reference']) ? $validated['reference'] : ('WAGE-'.now()->format('YmdHis'));

        DB::transaction(function () use (
            $validated,
            $project,
            $unpaidRecords,
            $totalAmount,
            $shiftCount,
            $uniqueWorkerCount,
            $dateLabel,
            $paymentRef
        ): void {
            if ($validated['payment_destination'] === 'direct_pay') {
                $category = ExpenseCategory::firstOrCreate(
                    ['name' => 'Labor'],
                    ['description' => 'Direct Workforce Wages & Labor Payouts']
                );

                $expense = Expense::create([
                    'project_id' => $project->id,
                    'expense_category_id' => $category->id,
                    'recorded_by' => auth()->id(),
                    'amount' => $totalAmount,
                    'expense_date' => $validated['payment_date'],
                    'vendor' => 'Workforce Direct Labor',
                    'payment_method' => ucfirst(str_replace('_', ' ', $validated['payment_method'])),
                    'description' => "Wage payout for {$uniqueWorkerCount} worker(s) ({$shiftCount} shifts, {$dateLabel})",
                    'notes' => $validated['notes'] ?? null,
                ]);

                $voucherRef = "EXP-#{$expense->id}";

                foreach ($unpaidRecords as $att) {
                    $att->update([
                        'is_paid' => true,
                        'paid_at' => now(),
                        'payment_method' => 'direct_pay:'.$validated['payment_method'],
                        'payment_reference' => $voucherRef,
                    ]);
                }

                ActivityLogger::log([
                    'project_id' => $project->id,
                    'event' => 'wages_paid_direct',
                    'description' => 'Disbursed PKR '.number_format($totalAmount, 2)." direct wages for {$uniqueWorkerCount} worker(s) ({$shiftCount} shifts, {$dateLabel})",
                    'properties' => [
                        'expense_id' => $expense->id,
                        'total_amount' => $totalAmount,
                        'worker_count' => $uniqueWorkerCount,
                        'shift_count' => $shiftCount,
                        'destination' => 'direct_pay',
                        'method' => $validated['payment_method'],
                    ],
                ], $expense);
            } else {
                // Contractor Pay: group by worker's contractor_id
                $grouped = $unpaidRecords->groupBy(fn ($rec) => $rec->worker?->contractor_id ?? 0);

                foreach ($grouped as $contractorId => $records) {
                    $subTotal = (float) $records->sum('payable_amount');
                    $subWorkerCount = $records->pluck('worker_id')->unique()->count();

                    if ($contractorId > 0 && ($contractor = Contractor::find($contractorId))) {
                        $payment = ContractorPayment::create([
                            'project_id' => $project->id,
                            'contractor_id' => $contractor->id,
                            'recorded_by' => auth()->id(),
                            'amount' => $subTotal,
                            'payment_date' => $validated['payment_date'],
                            'payment_type' => PaymentType::LaborWage->value,
                            'reference' => $paymentRef.'-'.$contractor->id,
                            'notes' => "Labor wage settlement for {$subWorkerCount} worker(s) ({$dateLabel}). ".($validated['notes'] ?? ''),
                            'is_voided' => false,
                        ]);

                        $voucherRef = 'VOUCHER-#'.str_pad($payment->id, 5, '0', STR_PAD_LEFT);

                        foreach ($records as $att) {
                            $att->update([
                                'is_paid' => true,
                                'paid_at' => now(),
                                'payment_method' => 'contractor_pay:'.$validated['payment_method'],
                                'payment_reference' => $voucherRef,
                            ]);
                        }

                        ActivityLogger::log([
                            'project_id' => $project->id,
                            'event' => 'contractor_payment_made',
                            'description' => 'Disbursed PKR '.number_format($subTotal, 2)." labor wages to contractor {$contractor->name} for {$subWorkerCount} worker(s) ({$dateLabel})",
                            'properties' => [
                                'payment_id' => $payment->id,
                                'contractor_id' => $contractor->id,
                                'amount' => $subTotal,
                                'destination' => 'contractor_pay',
                            ],
                        ], $payment);
                    } else {
                        // Direct workers without contractor in contractor-pay mode
                        $category = ExpenseCategory::firstOrCreate(
                            ['name' => 'Labor'],
                            ['description' => 'Direct Workforce Wages & Labor Payouts']
                        );

                        $expense = Expense::create([
                            'project_id' => $project->id,
                            'expense_category_id' => $category->id,
                            'recorded_by' => auth()->id(),
                            'amount' => $subTotal,
                            'expense_date' => $validated['payment_date'],
                            'vendor' => 'Direct Labor (Unassigned to Contractor)',
                            'payment_method' => ucfirst(str_replace('_', ' ', $validated['payment_method'])),
                            'description' => "Direct labor wage payout for {$subWorkerCount} worker(s) ({$dateLabel})",
                            'notes' => $validated['notes'] ?? null,
                        ]);

                        $voucherRef = "EXP-#{$expense->id}";

                        foreach ($records as $att) {
                            $att->update([
                                'is_paid' => true,
                                'paid_at' => now(),
                                'payment_method' => 'direct_pay:'.$validated['payment_method'],
                                'payment_reference' => $voucherRef,
                            ]);
                        }
                    }
                }
            }
        });

        return redirect()->back()->with(
            'success',
            'Successfully processed wage payout of PKR '.number_format($totalAmount, 2)." for {$uniqueWorkerCount} worker(s) ({$shiftCount} shifts, {$dateLabel}) via ".($validated['payment_destination'] === 'direct_pay' ? 'Direct Pay Expense' : 'Contractor Payment Voucher(s)').'.'
        );
    }

    public function destroy(Attendance $attendance): RedirectResponse
    {
        $date = $attendance->attendance_date->format('d M Y');
        $workerName = $attendance->worker->name;
        $projectId = $attendance->project_id;

        $attendance->delete();

        return redirect()->route('attendance.history', ['project_id' => $projectId])
            ->with('success', "Attendance record for {$workerName} on {$date} deleted.");
    }
}
