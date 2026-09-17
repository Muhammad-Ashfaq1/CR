@extends('layouts.app')

@section('title', 'Admin Dashboard — ' . config('app.name'))

@section('content')
<div class="cst-page-header">
    <div>
        <h1 class="cst-page-title">System Administration</h1>
        <p class="cst-page-subtitle">Platform-wide statistics, user management, and global configurations.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
            <i class="ti ti-user-plus me-1"></i> Add User
        </a>
        <a href="{{ route('admin.expense-categories.create') }}" class="btn btn-outline-secondary">
            <i class="ti ti-category me-1"></i> Add Category
        </a>
    </div>
</div>

{{-- Admin Stat Cards --}}
<div class="row g-4 mb-4">
    <div class="col-sm-6 col-xl-2">
        <div class="cst-card cst-stat-card">
            <div class="cst-stat-label">Projects</div>
            <div class="cst-stat-value">{{ $totalProjects }}</div>
            <div class="cst-stat-sub text-success">{{ $activeProjects }} active</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-2">
        <div class="cst-card cst-stat-card">
            <div class="cst-stat-label">Users</div>
            <div class="cst-stat-value">{{ $totalUsers }}</div>
            <div class="cst-stat-sub text-muted">Platform accounts</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-2">
        <div class="cst-card cst-stat-card">
            <div class="cst-stat-label">Contractors</div>
            <div class="cst-stat-value">{{ $totalContractors }}</div>
            <div class="cst-stat-sub text-muted">Registered partners</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-2">
        <div class="cst-card cst-stat-card">
            <div class="cst-stat-label">Workforce</div>
            <div class="cst-stat-value">{{ $totalWorkers }}</div>
            <div class="cst-stat-sub text-muted">Total workers</div>
        </div>
    </div>
    <div class="col-sm-12 col-xl-4">
        <div class="cst-card cst-stat-card">
            <div class="cst-stat-label">Total Platform Expenses</div>
            <div class="cst-stat-value text-primary">PKR {{ number_format($totalExpenses, 0) }}</div>
            <div class="cst-stat-sub text-muted">Recorded across all projects</div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="cst-card mb-4">
            <div class="cst-card-header d-flex justify-content-between align-items-center">
                <h5 class="cst-card-title mb-0">Recent Projects</h5>
                <a href="{{ route('projects.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="cst-card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Project</th>
                                <th>Owner</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentProjects as $project)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $project->name }}</div>
                                        <div class="small text-muted">{{ $project->location ?? 'No location' }}</div>
                                    </td>
                                    <td>{{ $project->owner?->name ?? '—' }}</td>
                                    <td>
                                        <span class="badge {{ $project->status->badgeClass() }}">{{ $project->status->label() }}</span>
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('projects.show', $project) }}" class="btn btn-sm btn-outline-primary">View</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-3 text-muted">No projects found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="cst-card mb-4">
            <div class="cst-card-header d-flex justify-content-between align-items-center">
                <h5 class="cst-card-title mb-0">Recent Activity Trail</h5>
                <a href="{{ route('activities.index') }}" class="btn btn-sm btn-outline-secondary">Full Log</a>
            </div>
            <div class="cst-card-body p-3">
                <div class="cst-timeline">
                    @forelse($recentLogs as $log)
                        <div class="d-flex mb-3 pb-2 border-bottom">
                            <div class="me-3">
                                <span class="badge bg-label-secondary rounded-circle p-2">
                                    <i class="ti ti-activity"></i>
                                </span>
                            </div>
                            <div class="flex-1 min-width-0">
                                <div class="small fw-semibold text-dark">{{ $log->description ?? $log->event }}</div>
                                <div class="text-muted" style="font-size: 0.75rem;">
                                    by {{ $log->user?->name ?? 'System' }} &bull; {{ $log->created_at->diffForHumans() }}
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-muted text-center py-3">No activity logs recorded yet.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
