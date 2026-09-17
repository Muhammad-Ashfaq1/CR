@extends('layouts.app')

@section('title', 'Admin Dashboard — ' . config('app.name'))

@section('content')
{{-- Banner Intro --}}
<div class="pos-glass-card pos-tone-primary mb-4">
    <div class="pos-glass-intro">
        <div class="pos-glass-intro-icon">
            <i class="icon-base ti tabler-shield-check" aria-hidden="true"></i>
        </div>
        <div class="pos-glass-intro-content">
            <div class="pos-glass-intro-title">
                <h4 class="mb-1 text-heading fw-bold">System Administration</h4>
                <span class="badge bg-label-danger">Central Admin</span>
            </div>
            <p class="pos-glass-intro-subtitle mb-0">Platform-wide statistics, user access management, and global construction expense categories.</p>
        </div>
        <div class="pos-glass-intro-actions">
            <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                <i class="icon-base ti tabler-user-plus me-1"></i> Add User
            </a>
            <a href="{{ route('admin.expense-categories.create') }}" class="btn btn-label-secondary">
                <i class="icon-base ti tabler-category me-1"></i> Add Category
            </a>
        </div>
    </div>
</div>

{{-- Admin KPI Row --}}
<div class="row g-4 mb-4">
    <div class="col-sm-6 col-xl-2">
        <div class="pos-glass-card pos-tone-primary h-100">
            <div class="pos-stat-body">
                <div class="pos-stat-head">
                    <span class="pos-stat-icon"><i class="icon-base ti tabler-building-skyscraper" aria-hidden="true"></i></span>
                    <h6 class="pos-stat-label">Projects</h6>
                </div>
                <p class="pos-stat-value">{{ $totalProjects }}</p>
                <div class="pos-stat-sub text-success">{{ $activeProjects }} active</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-2">
        <div class="pos-glass-card pos-tone-info h-100">
            <div class="pos-stat-body">
                <div class="pos-stat-head">
                    <span class="pos-stat-icon"><i class="icon-base ti tabler-users" aria-hidden="true"></i></span>
                    <h6 class="pos-stat-label">Users</h6>
                </div>
                <p class="pos-stat-value">{{ $totalUsers }}</p>
                <div class="pos-stat-sub text-muted">Accounts</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-2">
        <div class="pos-glass-card pos-tone-warning h-100">
            <div class="pos-stat-body">
                <div class="pos-stat-head">
                    <span class="pos-stat-icon"><i class="icon-base ti tabler-user-cog" aria-hidden="true"></i></span>
                    <h6 class="pos-stat-label">Contractors</h6>
                </div>
                <p class="pos-stat-value">{{ $totalContractors }}</p>
                <div class="pos-stat-sub text-muted">Partners</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-2">
        <div class="pos-glass-card pos-tone-success h-100">
            <div class="pos-stat-body">
                <div class="pos-stat-head">
                    <span class="pos-stat-icon"><i class="icon-base ti tabler-hammer" aria-hidden="true"></i></span>
                    <h6 class="pos-stat-label">Workforce</h6>
                </div>
                <p class="pos-stat-value">{{ $totalWorkers }}</p>
                <div class="pos-stat-sub text-muted">Site workers</div>
            </div>
        </div>
    </div>
    <div class="col-sm-12 col-xl-4">
        <div class="pos-glass-card pos-tone-danger h-100">
            <div class="pos-stat-body">
                <div class="pos-stat-head">
                    <span class="pos-stat-icon"><i class="icon-base ti tabler-receipt" aria-hidden="true"></i></span>
                    <h6 class="pos-stat-label">Platform Spend</h6>
                </div>
                <p class="pos-stat-value text-danger fs-4">PKR {{ number_format($totalExpenses, 0) }}</p>
                <div class="pos-stat-sub text-muted">Across all projects</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="pos-listing mb-4">
            <div class="pos-glass-card pos-tone-secondary pos-listing-panel">
                <div class="pos-listing-toolbar d-flex justify-content-between align-items-center">
                    <h5 class="pos-listing-title mb-0">Recent Projects</h5>
                    <a href="{{ route('projects.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
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
                                        <div class="fw-semibold text-heading">{{ $project->name }}</div>
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
                                    <td colspan="4" class="text-center py-4 text-muted">No projects found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="pos-glass-card pos-tone-secondary mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Recent Activity Trail</h5>
                <a href="{{ route('activities.index') }}" class="btn btn-sm btn-outline-secondary">Full Log</a>
            </div>
            <div class="card-body p-3">
                <div class="list-group list-group-flush">
                    @forelse($recentLogs as $log)
                        <div class="list-group-item d-flex px-0 py-2 border-bottom">
                            <div class="me-3">
                                <span class="badge bg-label-primary rounded-circle p-2">
                                    <i class="icon-base ti tabler-activity"></i>
                                </span>
                            </div>
                            <div class="flex-grow-1 min-w-0">
                                <div class="small fw-semibold text-heading">{{ $log->description ?? $log->event }}</div>
                                <div class="text-muted small" style="font-size: 0.75rem;">
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
