<?php

namespace App\Http\Controllers;

use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use App\Models\Project;
use App\Models\Worker;
use App\Services\ActivityLogger;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceController extends Controller
{
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
     * Attendance history and ledger view.
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

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('attendance_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('attendance_date', '<=', $request->to_date);
        }

        $summaryQuery = clone $query;
        $totalWages = (float) $summaryQuery->sum('payable_amount');
        $fullDaysCount = (clone $query)->where('status', 'full_day')->count();
        $halfDaysCount = (clone $query)->where('status', 'half_day')->count();
        $absentCount = (clone $query)->where('status', 'absent')->count();

        $records = $query->latest('attendance_date')->paginate(20)->withQueryString();

        $projects = $user->isOwner()
            ? Project::where('owner_id', $user->id)->get()
            : Project::all();

        $workers = Worker::where('status', 'active')->get();
        $statuses = AttendanceStatus::cases();

        return view('attendance.history', compact(
            'records',
            'projects',
            'workers',
            'statuses',
            'totalWages',
            'fullDaysCount',
            'halfDaysCount',
            'absentCount'
        ));
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
