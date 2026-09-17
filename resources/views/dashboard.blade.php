@extends('layouts.app')

@section('title', 'Dashboard — ' . config('app.name'))

@section('content')
@php
    $user = auth()->user();
@endphp

<div class="awt-dashboard">
    {{-- AWT Header --}}
    <div class="awt-dash-header mb-4">
        <div class="awt-dash-header-body">
            <div class="awt-dash-header-identity">
                <span class="awt-dash-header-accent" aria-hidden="true"></span>
                <div class="min-w-0">
                    <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                        <h5 class="awt-dash-header-title mb-0">Welcome back, {{ $user->name }}</h5>
                        <span class="badge bg-label-primary text-uppercase">{{ $user->role->label() }}</span>
                    </div>
                    <p class="awt-dash-header-subtitle mb-0">Live construction site telemetry, financial commitments, workforce attendance, and expense metrics.</p>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                @if($user->isOwner() || $user->isAdmin())
                    <a href="{{ route('projects.create') }}" class="btn btn-sm btn-primary">
                        <i class="icon-base ti tabler-plus me-1"></i> New Project
                    </a>
                    <a href="{{ route('expenses.create') }}" class="btn btn-sm btn-label-secondary">
                        <i class="icon-base ti tabler-receipt me-1"></i> Add Expense
                    </a>
                @endif
                <a href="{{ route('attendance.index') }}" class="btn btn-sm btn-warning">
                    <i class="icon-base ti tabler-calendar-check me-1"></i> Attendance
                </a>
            </div>
        </div>
    </div>

    @if($user->isOwner())
        {{-- KPI Cards Row --}}
        <div class="row g-3 g-md-4 mb-4">
            <div class="col-sm-6 col-xl-3">
                <div class="card h-100 awt-kpi-card awt-glass-card awt-tone-primary">
                    <div class="card-body awt-kpi-body awt-stat-body">
                        <div class="awt-kpi-head awt-stat-head">
                            <span class="awt-kpi-icon awt-stat-icon" aria-hidden="true">
                                <i class="icon-base ti tabler-building-skyscraper"></i>
                            </span>
                            <h6 class="awt-kpi-label awt-stat-label">Total Projects</h6>
                        </div>
                        <div class="awt-kpi-figure">
                            <p class="awt-kpi-value awt-stat-value">{{ $totalProjects }}</p>
                            <p class="awt-kpi-breakdown awt-stat-desc">
                                <span class="badge bg-label-success">{{ $activeProjects }} Active Sites</span>
                            </p>
                        </div>
                        <p class="awt-stat-note">
                            <a href="{{ route('projects.index') }}" class="text-decoration-none">Manage site directory &rarr;</a>
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-xl-3">
                <div class="card h-100 awt-kpi-card awt-glass-card awt-tone-warning">
                    <div class="card-body awt-kpi-body awt-stat-body">
                        <div class="awt-kpi-head awt-stat-head">
                            <span class="awt-kpi-icon awt-stat-icon" aria-hidden="true">
                                <i class="icon-base ti tabler-receipt"></i>
                            </span>
                            <h6 class="awt-kpi-label awt-stat-label">Direct Expenses</h6>
                        </div>
                        <div class="awt-kpi-figure">
                            <p class="awt-kpi-value awt-stat-value">PKR {{ number_format($totalExpenses, 0) }}</p>
                            <p class="awt-kpi-breakdown awt-stat-desc">Materials & Operations</p>
                        </div>
                        <p class="awt-stat-note">
                            <a href="{{ route('expenses.index') }}" class="text-decoration-none">View detailed ledger &rarr;</a>
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-xl-3">
                <div class="card h-100 awt-kpi-card awt-glass-card awt-tone-info">
                    <div class="card-body awt-kpi-body awt-stat-body">
                        <div class="awt-kpi-head awt-stat-head">
                            <span class="awt-kpi-icon awt-stat-icon" aria-hidden="true">
                                <i class="icon-base ti tabler-users"></i>
                            </span>
                            <h6 class="awt-kpi-label awt-stat-label">Total Workforce</h6>
                        </div>
                        <div class="awt-kpi-figure">
                            <p class="awt-kpi-value awt-stat-value">{{ $totalWorkers }}</p>
                            <p class="awt-kpi-breakdown awt-stat-desc">Registered Site Personnel</p>
                        </div>
                        <p class="awt-stat-note">
                            <a href="{{ route('workers.index') }}" class="text-decoration-none">Review roster &rarr;</a>
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-xl-3">
                <div class="card h-100 awt-kpi-card awt-glass-card awt-tone-success">
                    <div class="card-body awt-kpi-body awt-stat-body">
                        <div class="awt-kpi-head awt-stat-head">
                            <span class="awt-kpi-icon awt-stat-icon" aria-hidden="true">
                                <i class="icon-base ti tabler-chart-pie"></i>
                            </span>
                            <h6 class="awt-kpi-label awt-stat-label">Financial Analytics</h6>
                        </div>
                        <div class="awt-kpi-figure">
                            <p class="awt-kpi-value awt-stat-value fs-4">Overview</p>
                            <p class="awt-kpi-breakdown awt-stat-desc">Budget & Cost Audits</p>
                        </div>
                        <p class="awt-stat-note">
                            <a href="{{ route('reports.project-summary') }}" class="text-success fw-medium text-decoration-none">Project Summaries &rarr;</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Active Projects Table --}}
        <div class="awt-table-card awt-tone-secondary mb-4">
            <div class="awt-listing-toolbar">
                <div class="d-flex align-items-center gap-2">
                    <span class="awt-stat-icon" style="width: 2rem; height: 2rem; font-size: 1rem; border-radius: 0.5rem;" aria-hidden="true">
                        <i class="icon-base ti tabler-building-factory-2"></i>
                    </span>
                    <h5 class="mb-0 fw-semibold">Active Construction Projects</h5>
                </div>
                <a href="{{ route('projects.index') }}" class="btn btn-sm btn-outline-primary">
                    View All Projects &rarr;
                </a>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Project & Site</th>
                            <th>Location</th>
                            <th>Status</th>
                            <th>Contractors</th>
                            <th>Direct Expenses</th>
                            <th>Workers</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($projects as $project)
                            <tr>
                                <td>
                                    <a href="{{ route('projects.show', $project) }}" class="fw-semibold text-heading text-decoration-none">
                                        {{ $project->name }}
                                    </a>
                                    @if($project->site_name)
                                        <div class="text-muted small">{{ $project->site_name }}</div>
                                    @endif
                                </td>
                                <td>
                                    <span class="text-muted small">
                                        <i class="icon-base ti tabler-map-pin me-1"></i>{{ $project->location ?? '—' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge {{ $project->status->badgeClass() }}">{{ $project->status->label() }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-label-info">{{ $project->contractors->count() }} Contractors</span>
                                </td>
                                <td class="fw-semibold text-heading">
                                    PKR {{ number_format($project->totalExpenses(), 0) }}
                                </td>
                                <td>
                                    <span class="badge bg-label-secondary">{{ $project->workers_count }}</span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('projects.show', $project) }}" class="btn btn-sm btn-outline-primary">
                                        Open Hub &rarr;
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <div class="awt-empty-state">
                                        <span class="awt-empty-state-icon">
                                            <i class="icon-base ti tabler-building-factory-2"></i>
                                        </span>
                                        <h6 class="awt-empty-state-title">No projects created yet</h6>
                                        <p class="awt-empty-state-desc">Start organizing your construction operations by creating your first project workspace.</p>
                                        <a href="{{ route('projects.create') }}" class="btn btn-sm btn-primary">
                                            <i class="icon-base ti tabler-plus me-1"></i> Create Your First Project
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    @elseif($user->isContractor())
        {{-- Contractor View --}}
        <div class="row g-3 g-md-4 mb-4">
            <div class="col-sm-6 col-xl-4">
                <div class="card h-100 awt-kpi-card awt-glass-card awt-tone-primary">
                    <div class="card-body awt-kpi-body awt-stat-body">
                        <div class="awt-kpi-head awt-stat-head">
                            <span class="awt-kpi-icon awt-stat-icon" aria-hidden="true">
                                <i class="icon-base ti tabler-building-skyscraper"></i>
                            </span>
                            <h6 class="awt-kpi-label awt-stat-label">Assigned Projects</h6>
                        </div>
                        <div class="awt-kpi-figure">
                            <p class="awt-kpi-value awt-stat-value">{{ $projects->count() }}</p>
                            <p class="awt-kpi-breakdown awt-stat-desc">Active Work Sites</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-xl-4">
                <div class="card h-100 awt-kpi-card awt-glass-card awt-tone-info">
                    <div class="card-body awt-kpi-body awt-stat-body">
                        <div class="awt-kpi-head awt-stat-head">
                            <span class="awt-kpi-icon awt-stat-icon" aria-hidden="true">
                                <i class="icon-base ti tabler-users"></i>
                            </span>
                            <h6 class="awt-kpi-label awt-stat-label">My Workforce</h6>
                        </div>
                        <div class="awt-kpi-figure">
                            <p class="awt-kpi-value awt-stat-value">{{ $totalWorkers }}</p>
                            <p class="awt-kpi-breakdown awt-stat-desc">
                                <span class="badge bg-label-success">{{ $activeWorkers }} Active</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-xl-4">
                <div class="card h-100 awt-kpi-card awt-glass-card awt-tone-warning">
                    <div class="card-body awt-kpi-body awt-stat-body">
                        <div class="awt-kpi-head awt-stat-head">
                            <span class="awt-kpi-icon awt-stat-icon" aria-hidden="true">
                                <i class="icon-base ti tabler-bolt"></i>
                            </span>
                            <h6 class="awt-kpi-label awt-stat-label">Quick Actions</h6>
                        </div>
                        <div class="mt-2 d-flex flex-column gap-2">
                            <a href="{{ route('attendance.index') }}" class="btn btn-sm btn-warning w-100">
                                <i class="icon-base ti tabler-calendar-check me-1"></i> Mark Today's Attendance
                            </a>
                            <a href="{{ route('workers.create') }}" class="btn btn-sm btn-outline-secondary w-100">
                                <i class="icon-base ti tabler-plus me-1"></i> Add Worker
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Contractor Projects Table --}}
        <div class="awt-table-card awt-tone-secondary mb-4">
            <div class="awt-listing-toolbar">
                <h5 class="mb-0 fw-semibold">My Assigned Projects</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Project</th>
                            <th>Location</th>
                            <th>Contract Amount</th>
                            <th>Workers</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($projects as $proj)
                            <tr>
                                <td>
                                    <div class="fw-semibold text-heading">{{ $proj->name }}</div>
                                    <div class="small text-muted">{{ $proj->site_name }}</div>
                                </td>
                                <td>{{ $proj->location ?? '—' }}</td>
                                <td class="fw-bold text-success">
                                    PKR {{ number_format($proj->pivot?->contract_amount ?? 0, 0) }}
                                </td>
                                <td>
                                    <span class="badge bg-label-secondary">{{ $proj->workers_count }}</span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('attendance.index', ['project_id' => $proj->id]) }}" class="btn btn-sm btn-warning">
                                        <i class="icon-base ti tabler-calendar-check me-1"></i> Attendance
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
                                    <div class="awt-empty-state">
                                        <span class="awt-empty-state-icon">
                                            <i class="icon-base ti tabler-folder-off"></i>
                                        </span>
                                        <h6 class="awt-empty-state-title">No projects assigned yet</h6>
                                        <p class="awt-empty-state-desc">You have not been assigned to any active construction projects.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>

@endsection
