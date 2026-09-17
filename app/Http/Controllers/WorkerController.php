<?php

namespace App\Http\Controllers;

use App\Models\Contractor;
use App\Models\Project;
use App\Models\Worker;
use App\Models\WorkerWageHistory;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WorkerController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();
        $query = Worker::query()->with(['contractor', 'project'])->withCount('attendance');

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

        if ($request->filled('contractor_id')) {
            $query->where('contractor_id', $request->contractor_id);
        }

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('worker_type')) {
            $query->where('worker_type', $request->worker_type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $workers = $query->latest()->paginate(15)->withQueryString();

        $contractors = Contractor::where('is_active', true)->get();
        $projects = $user->isOwner()
            ? Project::where('owner_id', $user->id)->get()
            : Project::all();

        $workerTypes = ['Mason', 'Laborer', 'Carpenter', 'Electrician', 'Plumber', 'Steel Fixer', 'Painter', 'Welder', 'Tile Fixer', 'Supervisor', 'Other'];

        return view('workers.index', compact('workers', 'contractors', 'projects', 'workerTypes'));
    }

    public function create(): View
    {
        $user = auth()->user();

        $contractors = $user->isContractor()
            ? Contractor::where('id', $user->contractorProfile?->id)->get()
            : Contractor::where('is_active', true)->get();

        $projects = $user->isOwner()
            ? Project::where('owner_id', $user->id)->get()
            : ($user->isContractor()
                ? $user->contractorProfile?->projects ?? collect()
                : Project::all());

        $workerTypes = ['Mason', 'Laborer', 'Carpenter', 'Electrician', 'Plumber', 'Steel Fixer', 'Painter', 'Welder', 'Tile Fixer', 'Supervisor', 'Other'];

        return view('workers.create', compact('contractors', 'projects', 'workerTypes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'contractor_id' => ['required', 'exists:contractors,id'],
            'project_id' => ['nullable', 'exists:projects,id'],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'worker_type' => ['nullable', 'string', 'max:100'],
            'daily_wage' => ['required', 'numeric', 'min:0'],
            'joining_date' => ['nullable', 'date'],
            'status' => ['required', 'in:active,inactive'],
            'notes' => ['nullable', 'string'],
        ]);

        $worker = Worker::create([
            'contractor_id' => $validated['contractor_id'],
            'project_id' => $validated['project_id'] ?? null,
            'name' => $validated['name'],
            'phone' => $validated['phone'] ?? null,
            'worker_type' => $validated['worker_type'] ?? 'Laborer',
            'daily_wage' => $validated['daily_wage'],
            'joining_date' => $validated['joining_date'] ?? now()->toDateString(),
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?? null,
        ]);

        // Record initial wage history
        WorkerWageHistory::create([
            'worker_id' => $worker->id,
            'daily_wage' => $worker->daily_wage,
            'effective_from' => $worker->joining_date ?? now()->toDateString(),
            'changed_by' => auth()->id(),
            'reason' => 'Initial wage upon registration',
        ]);

        ActivityLogger::log([
            'project_id' => $worker->project_id,
            'event' => 'worker_created',
            'description' => "Registered worker {$worker->name} ({$worker->worker_type}) with daily wage PKR ".number_format($worker->daily_wage),
            'properties' => ['worker_id' => $worker->id, 'wage' => $worker->daily_wage],
        ], $worker);

        return redirect()->route('workers.show', $worker)
            ->with('success', "Worker '{$worker->name}' added successfully.");
    }

    public function show(Worker $worker): View
    {
        $worker->load([
            'contractor',
            'project',
            'wageHistory.changedBy',
            'attendance' => fn ($q) => $q->latest('attendance_date')->take(20),
        ]);

        $attendanceSummary = [
            'total' => $worker->attendance()->count(),
            'full_day' => $worker->attendance()->where('status', 'full_day')->count(),
            'half_day' => $worker->attendance()->where('status', 'half_day')->count(),
            'absent' => $worker->attendance()->where('status', 'absent')->count(),
            'leave' => $worker->attendance()->where('status', 'leave')->count(),
            'total_pay' => (float) $worker->attendance()->sum('payable_amount'),
        ];

        return view('workers.show', compact('worker', 'attendanceSummary'));
    }

    public function edit(Worker $worker): View
    {
        $user = auth()->user();

        $contractors = $user->isContractor()
            ? Contractor::where('id', $user->contractorProfile?->id)->get()
            : Contractor::where('is_active', true)->get();

        $projects = $user->isOwner()
            ? Project::where('owner_id', $user->id)->get()
            : ($user->isContractor()
                ? $user->contractorProfile?->projects ?? collect()
                : Project::all());

        $workerTypes = ['Mason', 'Laborer', 'Carpenter', 'Electrician', 'Plumber', 'Steel Fixer', 'Painter', 'Welder', 'Tile Fixer', 'Supervisor', 'Other'];

        return view('workers.edit', compact('worker', 'contractors', 'projects', 'workerTypes'));
    }

    public function update(Request $request, Worker $worker): RedirectResponse
    {
        $validated = $request->validate([
            'contractor_id' => ['required', 'exists:contractors,id'],
            'project_id' => ['nullable', 'exists:projects,id'],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'worker_type' => ['nullable', 'string', 'max:100'],
            'daily_wage' => ['required', 'numeric', 'min:0'],
            'wage_effective_date' => ['nullable', 'date'],
            'wage_change_reason' => ['nullable', 'string', 'max:255'],
            'joining_date' => ['nullable', 'date'],
            'status' => ['required', 'in:active,inactive'],
            'notes' => ['nullable', 'string'],
        ]);

        $oldWage = (float) $worker->daily_wage;
        $newWage = (float) $validated['daily_wage'];

        $worker->update([
            'contractor_id' => $validated['contractor_id'],
            'project_id' => $validated['project_id'] ?? null,
            'name' => $validated['name'],
            'phone' => $validated['phone'] ?? null,
            'worker_type' => $validated['worker_type'] ?? 'Laborer',
            'daily_wage' => $newWage,
            'joining_date' => $validated['joining_date'] ?? $worker->joining_date,
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?? null,
        ]);

        // If wage changed, record in history preserving historical data
        if ($oldWage !== $newWage) {
            WorkerWageHistory::create([
                'worker_id' => $worker->id,
                'daily_wage' => $newWage,
                'effective_from' => $validated['wage_effective_date'] ?? now()->toDateString(),
                'changed_by' => auth()->id(),
                'reason' => $validated['wage_change_reason'] ?? "Wage updated from PKR {$oldWage} to PKR {$newWage}",
            ]);

            ActivityLogger::log([
                'project_id' => $worker->project_id,
                'event' => 'worker_wage_changed',
                'description' => "Changed wage for {$worker->name} from PKR {$oldWage} to PKR {$newWage}",
                'properties' => ['worker_id' => $worker->id, 'old_wage' => $oldWage, 'new_wage' => $newWage],
            ], $worker);
        }

        ActivityLogger::log([
            'project_id' => $worker->project_id,
            'event' => 'worker_updated',
            'description' => "Updated worker {$worker->name}",
            'properties' => ['worker_id' => $worker->id],
        ], $worker);

        return redirect()->route('workers.show', $worker)
            ->with('success', "Worker '{$worker->name}' updated successfully.");
    }

    public function destroy(Worker $worker): RedirectResponse
    {
        $name = $worker->name;
        $worker->delete();

        ActivityLogger::log([
            'event' => 'worker_deleted',
            'description' => "Deleted worker {$name}",
        ]);

        return redirect()->route('workers.index')
            ->with('success', "Worker '{$name}' deleted successfully.");
    }
}
