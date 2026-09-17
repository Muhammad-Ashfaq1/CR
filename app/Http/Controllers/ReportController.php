<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Contractor;
use App\Models\ContractorPayment;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Project;
use App\Models\Worker;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(): View
    {
        $totalProjects = Project::count();
        $totalContractorPaid = (float) ContractorPayment::where('is_voided', false)->sum('amount');
        $totalExpenses = (float) Expense::sum('amount');
        $totalWagesRecorded = (float) Attendance::sum('payable_amount');
        $totalInvestment = $totalContractorPaid + $totalExpenses;

        $projects = Project::with(['contractors', 'expenses'])->get();

        return view('reports.index', compact(
            'totalProjects',
            'totalContractorPaid',
            'totalExpenses',
            'totalWagesRecorded',
            'totalInvestment',
            'projects'
        ));
    }

    public function expenses(Request $request): View
    {
        $query = Expense::query()->with(['project', 'category', 'recordedBy']);

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('category_id')) {
            $query->where('expense_category_id', $request->category_id);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('expense_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('expense_date', '<=', $request->to_date);
        }

        $expenses = $query->orderBy('expense_date', 'desc')->get();
        $totalAmount = (float) $expenses->sum('amount');

        // Breakdown by category
        $categoryBreakdown = $expenses->groupBy('expense_category_id')->map(function ($group) {
            return [
                'category' => $group->first()->category?->name ?? 'Uncategorized',
                'color' => $group->first()->category?->color ?? '#94a3b8',
                'count' => $group->count(),
                'total' => (float) $group->sum('amount'),
            ];
        })->values();

        $projects = Project::all();
        $categories = ExpenseCategory::all();

        return view('reports.expenses', compact('expenses', 'totalAmount', 'categoryBreakdown', 'projects', 'categories'));
    }

    public function wages(Request $request): View
    {
        $query = Attendance::query()->with(['worker.contractor', 'project']);

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('contractor_id')) {
            $contractorId = $request->contractor_id;
            $query->whereHas('worker', fn ($q) => $q->where('contractor_id', $contractorId));
        }

        if ($request->filled('from_date')) {
            $query->whereDate('attendance_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('attendance_date', '<=', $request->to_date);
        }

        $attendanceRecords = $query->orderBy('attendance_date', 'desc')->get();
        $totalWages = (float) $attendanceRecords->sum('payable_amount');

        // Worker-level rollup
        $workerSummary = $attendanceRecords->groupBy('worker_id')->map(function ($records) {
            $worker = $records->first()->worker;

            return [
                'worker' => $worker,
                'full_days' => $records->where('status.value', 'full_day')->count(),
                'half_days' => $records->where('status.value', 'half_day')->count(),
                'absent' => $records->where('status.value', 'absent')->count(),
                'leave' => $records->where('status.value', 'leave')->count(),
                'total_days' => $records->count(),
                'total_pay' => (float) $records->sum('payable_amount'),
            ];
        })->values();

        $projects = Project::all();
        $contractors = Contractor::all();

        return view('reports.wages', compact('attendanceRecords', 'totalWages', 'workerSummary', 'projects', 'contractors'));
    }

    public function contractorLedger(Request $request): View
    {
        $contractors = Contractor::with('projects')->get();
        $selectedContractorId = $request->query('contractor_id', $contractors->first()?->id);
        $selectedProjectId = $request->query('project_id');

        $contractor = $selectedContractorId ? Contractor::find($selectedContractorId) : null;
        $ledgerEntries = collect();
        $totalContract = 0.0;
        $totalPaid = 0.0;
        $remaining = 0.0;

        if ($contractor) {
            $projectsQuery = $contractor->projects();
            if ($selectedProjectId) {
                $projectsQuery->where('projects.id', $selectedProjectId);
            }
            $assignedProjects = $projectsQuery->get();

            $totalContract = (float) $assignedProjects->sum('pivot.contract_amount');

            $paymentsQuery = ContractorPayment::where('contractor_id', $contractor->id)
                ->where('is_voided', false)
                ->with('project');

            if ($selectedProjectId) {
                $paymentsQuery->where('project_id', $selectedProjectId);
            }

            $payments = $paymentsQuery->orderBy('payment_date', 'asc')->get();
            $totalPaid = (float) $payments->sum('amount');
            $remaining = $totalContract - $totalPaid;

            $runningBalance = $totalContract;
            $ledgerEntries = $payments->map(function ($payment) use (&$runningBalance) {
                $runningBalance -= (float) $payment->amount;

                return [
                    'date' => $payment->payment_date,
                    'project' => $payment->project?->name,
                    'type' => $payment->payment_type->label(),
                    'reference' => $payment->reference,
                    'notes' => $payment->notes,
                    'amount' => (float) $payment->amount,
                    'running_balance' => $runningBalance,
                    'payment' => $payment,
                ];
            });
        }

        return view('reports.contractor-ledger', compact(
            'contractors',
            'selectedContractorId',
            'selectedProjectId',
            'contractor',
            'ledgerEntries',
            'totalContract',
            'totalPaid',
            'remaining'
        ));
    }

    public function projectSummary(Request $request): View
    {
        $projects = Project::with(['contractors', 'expenses.category'])->get();
        $selectedProjectId = $request->query('project_id', $projects->first()?->id);
        $project = $selectedProjectId ? Project::find($selectedProjectId) : null;

        $stats = null;
        if ($project) {
            $totalContract = $project->totalContractAmount();
            $contractorPaid = $project->totalContractorPaid();
            $contractorRemaining = $project->contractorRemaining();

            $materials = $project->totalExpensesByType('material');
            $labor = $project->totalExpensesByType('labor');
            $equipment = $project->totalExpensesByType('equipment');
            $utilities = $project->totalExpensesByType('utilities');
            $misc = (float) $project->expenses()
                ->whereHas('category', fn ($q) => $q->whereNotIn('type', ['material', 'labor', 'equipment', 'utilities']))
                ->sum('amount');

            $totalExpenses = $project->totalExpenses();
            $grandTotalSpent = $contractorPaid + $totalExpenses;
            $totalCommitment = $totalContract + $totalExpenses;

            $workerCount = Worker::where('project_id', $project->id)->count();
            $attendanceDaysCount = Attendance::where('project_id', $project->id)->count();
            $totalWagesRecorded = (float) Attendance::where('project_id', $project->id)->sum('payable_amount');

            $stats = compact(
                'totalContract',
                'contractorPaid',
                'contractorRemaining',
                'materials',
                'labor',
                'equipment',
                'utilities',
                'misc',
                'totalExpenses',
                'grandTotalSpent',
                'totalCommitment',
                'workerCount',
                'attendanceDaysCount',
                'totalWagesRecorded'
            );
        }

        return view('reports.project-summary', compact('projects', 'selectedProjectId', 'project', 'stats'));
    }
}
