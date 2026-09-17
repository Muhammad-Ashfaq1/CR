<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Contractor;
use App\Models\Expense;
use App\Models\Project;
use App\Models\User;
use App\Models\Worker;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $totalProjects = Project::count();
        $activeProjects = Project::where('status', 'active')->count();
        $totalUsers = User::count();
        $totalContractors = Contractor::count();
        $totalWorkers = Worker::count();
        $totalExpenses = (float) Expense::sum('amount');

        $recentProjects = Project::with(['owner', 'contractors'])->latest()->take(5)->get();
        $recentUsers = User::latest()->take(5)->get();
        $recentLogs = ActivityLog::with('user')->latest()->take(8)->get();

        return view('admin.dashboard', compact(
            'totalProjects',
            'activeProjects',
            'totalUsers',
            'totalContractors',
            'totalWorkers',
            'totalExpenses',
            'recentProjects',
            'recentUsers',
            'recentLogs'
        ));
    }
}
