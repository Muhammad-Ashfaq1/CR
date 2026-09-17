<?php

namespace App\Http\Controllers;

use App\Models\Contractor;
use App\Models\Expense;
use App\Models\Project;
use App\Models\Worker;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();

        if ($user->isAdmin()) {
            return $this->adminDashboard();
        }

        if ($user->isContractor()) {
            return $this->contractorDashboard();
        }

        return $this->ownerDashboard();
    }

    private function ownerDashboard(): View
    {
        $user = auth()->user();

        $projects = Project::where('owner_id', $user->id)
            ->withCount(['expenses', 'workers'])
            ->latest()
            ->take(5)
            ->get();

        $totalProjects = Project::where('owner_id', $user->id)->count();
        $activeProjects = Project::where('owner_id', $user->id)->where('status', 'active')->count();
        $totalExpenses = Expense::whereIn('project_id', Project::where('owner_id', $user->id)->pluck('id'))->sum('amount');
        $totalWorkers = Worker::whereIn('project_id', Project::where('owner_id', $user->id)->pluck('id'))->count();

        return view('dashboard', compact('projects', 'totalProjects', 'activeProjects', 'totalExpenses', 'totalWorkers'));
    }

    private function contractorDashboard(): View
    {
        $user = auth()->user();
        $contractor = $user->contractorProfile;

        $projects = $contractor?->projects()->withCount(['workers'])->latest()->take(5)->get() ?? collect();
        $totalWorkers = Worker::where('contractor_id', $contractor?->id)->count();
        $activeWorkers = Worker::where('contractor_id', $contractor?->id)->where('status', 'active')->count();

        return view('dashboard', compact('projects', 'totalWorkers', 'activeWorkers', 'contractor'));
    }

    private function adminDashboard(): View
    {
        $totalProjects = Project::count();
        $activeProjects = Project::where('status', 'active')->count();
        $totalContractors = Contractor::count();
        $totalWorkers = Worker::count();
        $totalExpenses = Expense::sum('amount');

        $recentProjects = Project::with('owner')->latest()->take(5)->get();

        return view('admin.dashboard', compact(
            'totalProjects', 'activeProjects', 'totalContractors', 'totalWorkers', 'totalExpenses', 'recentProjects'
        ));
    }
}
