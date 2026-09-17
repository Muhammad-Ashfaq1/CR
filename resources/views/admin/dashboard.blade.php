@extends('layouts.app')

@section('title', 'Admin Dashboard — ' . config('app.name'))

@section('content')
<div class="awt-dashboard">
    {{-- AWT Admin Header --}}
    <div class="awt-dash-header mb-4">
        <div class="awt-dash-header-body">
            <div class="awt-dash-header-identity">
                <span class="awt-dash-header-accent" aria-hidden="true"></span>
                <div class="min-w-0">
                    <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                        <h5 class="awt-dash-header-title mb-0">System Administration</h5>
                        <span class="badge bg-label-danger">Central Admin</span>
                    </div>
                    <p class="awt-dash-header-subtitle mb-0">Platform-wide statistics, user access management, and global construction expense categories.</p>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <a href="{{ route('admin.users.create') }}" class="btn btn-sm btn-primary">
                    <i class="icon-base ti tabler-user-plus me-1"></i> Add User
                </a>
                <a href="{{ route('admin.expense-categories.create') }}" class="btn btn-sm btn-label-secondary">
                    <i class="icon-base ti tabler-category me-1"></i> Add Category
                </a>
            </div>
        </div>
    </div>

    {{-- Admin KPI Row --}}
    <div class="row g-3 g-md-4 mb-4">
        <div class="col-sm-6 col-xl-2">
            <div class="card h-100 awt-kpi-card awt-glass-card awt-tone-primary">
                <div class="card-body awt-kpi-body awt-stat-body">
                    <div class="awt-kpi-head awt-stat-head">
                        <span class="awt-kpi-icon awt-stat-icon" aria-hidden="true">
                            <i class="icon-base ti tabler-building-skyscraper"></i>
                        </span>
                        <h6 class="awt-kpi-label awt-stat-label">Projects</h6>
                    </div>
                    <div class="awt-kpi-figure">
                        <p class="awt-kpi-value awt-stat-value">{{ $totalProjects }}</p>
                        <p class="awt-kpi-breakdown awt-stat-desc">
                            <span class="text-success fw-semibold">{{ $activeProjects }} active</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-2">
            <div class="card h-100 awt-kpi-card awt-glass-card awt-tone-info">
                <div class="card-body awt-kpi-body awt-stat-body">
                    <div class="awt-kpi-head awt-stat-head">
                        <span class="awt-kpi-icon awt-stat-icon" aria-hidden="true">
                            <i class="icon-base ti tabler-users"></i>
                        </span>
                        <h6 class="awt-kpi-label awt-stat-label">Users</h6>
                    </div>
                    <div class="awt-kpi-figure">
                        <p class="awt-kpi-value awt-stat-value">{{ $totalUsers }}</p>
                        <p class="awt-kpi-breakdown awt-stat-desc">Accounts</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-2">
            <div class="card h-100 awt-kpi-card awt-glass-card awt-tone-warning">
                <div class="card-body awt-kpi-body awt-stat-body">
                    <div class="awt-kpi-head awt-stat-head">
                        <span class="awt-kpi-icon awt-stat-icon" aria-hidden="true">
                            <i class="icon-base ti tabler-user-cog"></i>
                        </span>
                        <h6 class="awt-kpi-label awt-stat-label">Contractors</h6>
                    </div>
                    <div class="awt-kpi-figure">
                        <p class="awt-kpi-value awt-stat-value">{{ $totalContractors }}</p>
                        <p class="awt-kpi-breakdown awt-stat-desc">Partners</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-2">
            <div class="card h-100 awt-kpi-card awt-glass-card awt-tone-success">
                <div class="card-body awt-kpi-body awt-stat-body">
                    <div class="awt-kpi-head awt-stat-head">
                        <span class="awt-kpi-icon awt-stat-icon" aria-hidden="true">
                            <i class="icon-base ti tabler-hammer"></i>
                        </span>
                        <h6 class="awt-kpi-label awt-stat-label">Workforce</h6>
                    </div>
                    <div class="awt-kpi-figure">
                        <p class="awt-kpi-value awt-stat-value">{{ $totalWorkers }}</p>
                        <p class="awt-kpi-breakdown awt-stat-desc">Site workers</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-12 col-xl-4">
            <div class="card h-100 awt-kpi-card awt-glass-card awt-tone-danger">
                <div class="card-body awt-kpi-body awt-stat-body">
                    <div class="awt-kpi-head awt-stat-head">
                        <span class="awt-kpi-icon awt-stat-icon" aria-hidden="true">
                            <i class="icon-base ti tabler-receipt"></i>
                        </span>
                        <h6 class="awt-kpi-label awt-stat-label">Platform Spend</h6>
                    </div>
                    <div class="awt-kpi-figure">
                        <p class="awt-kpi-value awt-stat-value text-danger fs-3">PKR {{ number_format($totalExpenses, 0) }}</p>
                        <p class="awt-kpi-breakdown awt-stat-desc">Across all construction projects</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- Recent Projects Table --}}
        <div class="col-lg-7">
            <div class="awt-table-card awt-tone-secondary mb-4">
                <div class="awt-listing-toolbar">
                    <h5 class="mb-0 fw-semibold">Recent Projects</h5>
                    <a href="{{ route('projects.index') }}" class="btn btn-sm btn-outline-primary">View All &rarr;</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
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
                                        <div class="small text-muted">{{ $project->location ?? 'No location specified' }}</div>
                                    </td>
                                    <td>{{ $project->owner?->name ?? '—' }}</td>
                                    <td>
                                        <span class="badge {{ $project->status->badgeClass() }}">{{ $project->status->label() }}</span>
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('projects.show', $project) }}" class="btn btn-sm btn-outline-primary">
                                            Open &rarr;
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4">
                                        <div class="awt-empty-state py-4">
                                            <p class="text-muted mb-0">No projects found.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Activity Trail --}}
        <div class="col-lg-5">
            <div class="card awt-glass-card awt-tone-secondary mb-4">
                <div class="awt-listing-toolbar">
                    <h5 class="mb-0 fw-semibold">Recent Activity Trail</h5>
                    <a href="{{ route('activities.index') }}" class="btn btn-sm btn-outline-secondary">Full Log &rarr;</a>
                </div>
                <div class="card-body p-3">
                    <div class="list-group list-group-flush bg-transparent">
                        @forelse($recentLogs as $log)
                            <div class="list-group-item bg-transparent d-flex px-0 py-2 border-bottom border-light">
                                <div class="me-3">
                                    <span class="awt-stat-icon" style="width: 2.25rem; height: 2.25rem; font-size: 0.95rem; border-radius: 0.5rem;" aria-hidden="true">
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
                            <div class="text-muted text-center py-4">No activity logs recorded yet.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
