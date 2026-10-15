<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Contractor;
use App\Models\ContractorPayment;
use App\Models\Project;
use App\Models\User;
use App\Models\Worker;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();
        $query = ActivityLog::query()->with(['user', 'project']);

        if ($user->isOwner()) {
            $projectIds = Project::where('owner_id', $user->id)->pluck('id');
            $query->where(function ($q) use ($projectIds, $user) {
                $q->whereIn('project_id', $projectIds)
                    ->orWhere('user_id', $user->id);
            });
        } elseif ($user->isContractor()) {
            $contractor = $user->contractorProfile;
            if (! $contractor) {
                $query->whereRaw('1 = 0');
            } else {
                $workerIds = $contractor->workers()->pluck('id');
                $paymentIds = $contractor->payments()->pluck('id');
                $assignedProjectIds = $contractor->projects()->pluck('projects.id');

                // Strictly exclude any expense events
                $query->where('event', 'not like', 'expense_%');

                // Limit logs to this contractor's workers, payments, self-actions, and profile
                $query->where(function ($q) use ($user, $contractor, $workerIds, $paymentIds) {
                    $q->where('user_id', $user->id)
                        ->orWhere(function ($sub) use ($contractor) {
                            $sub->where('subject_type', Contractor::class)
                                ->where('subject_id', $contractor->id);
                        })
                        ->orWhere(function ($sub) use ($workerIds) {
                            $sub->where('subject_type', Worker::class)
                                ->whereIn('subject_id', $workerIds);
                        })
                        ->orWhere(function ($sub) use ($paymentIds) {
                            $sub->where('subject_type', ContractorPayment::class)
                                ->whereIn('subject_id', $paymentIds);
                        })
                        ->orWhere(function ($sub) use ($contractor) {
                            $sub->where('properties->contractor_id', $contractor->id);
                        });
                });

                // Ensure any project linked to the log is an assigned project
                $query->where(function ($q) use ($assignedProjectIds) {
                    $q->whereNull('project_id')
                        ->orWhereIn('project_id', $assignedProjectIds);
                });
            }
        }

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $eventsQuery = (clone $query)->reorder();
        $logs = $query->latest()->paginate(25)->withQueryString();

        if ($user->isContractor()) {
            $contractor = $user->contractorProfile;
            $projects = $contractor ? $contractor->projects : collect();
            $users = User::where('id', $user->id)->get();
            $events = $eventsQuery->distinct()->pluck('event')->filter();
        } else {
            $projects = $user->isOwner()
                ? Project::where('owner_id', $user->id)->get()
                : Project::all();
            $users = User::all();
            $events = ActivityLog::distinct()->pluck('event')->filter();
        }

        return view('activities.index', compact('logs', 'projects', 'users', 'events'));
    }
}
