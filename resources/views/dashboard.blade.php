@extends('layouts.app')

@section('title', 'Dashboard — ' . config('app.name'))

@section('content')
@php
    $user = auth()->user();
@endphp

<div class="cst-page-header">
    <div>
        <h1 class="cst-page-title">Welcome back, {{ $user->name }}!</h1>
        <p class="cst-page-subtitle">Here is the latest progress and financial overview across your construction sites.</p>
    </div>
    <div class="d-flex gap-2">
        @if($user->isOwner() || $user->isAdmin())
            <a href="{{ route('projects.create') }}" class="btn btn-primary">
                <i class="ti ti-plus me-1"></i> New Project
            </a>
            <a href="{{ route('expenses.create') }}" class="btn btn-outline-secondary">
                <i class="ti ti-receipt me-1"></i> Add Expense
            </a>
        @endif
        <a href="{{ route('attendance.index') }}" class="btn btn-warning text-dark">
            <i class="ti ti-calendar-check me-1"></i> Mark Attendance
        </a>
    </div>
</div>

@if($user->isOwner())
    {{-- Owner Stat Cards --}}
    <div class="row g-4 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="cst-card cst-stat-card">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="cst-stat-label">Total Projects</span>
                    <div class="cst-stat-icon tone-primary">
                        <i class="ti ti-building-skyscraper"></i>
                    </div>
                </div>
                <div class="cst-stat-value">{{ $totalProjects }}</div>
                <div class="cst-stat-sub">
                    <span class="badge bg-label-success">{{ $activeProjects }} Active</span>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="cst-card cst-stat-card">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="cst-stat-label">Direct Expenses</span>
                    <div class="cst-stat-icon tone-amber">
                        <i class="ti ti-receipt"></i>
                    </div>
                </div>
                <div class="cst-stat-value">PKR {{ number_format($totalExpenses, 0) }}</div>
                <div class="cst-stat-sub text-muted">Materials & Site Expenses</div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="cst-card cst-stat-card">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="cst-stat-label">Active Workforce</span>
                    <div class="cst-stat-icon tone-info">
                        <i class="ti ti-users"></i>
                    </div>
                </div>
                <div class="cst-stat-value">{{ $totalWorkers }}</div>
                <div class="cst-stat-sub text-muted">Assigned Workers</div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="cst-card cst-stat-card">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="cst-stat-label">Quick Reports</span>
                    <div class="cst-stat-icon tone-success">
                        <i class="ti ti-chart-pie"></i>
                    </div>
                </div>
                <div class="cst-stat-value"><a href="{{ route('reports.index') }}" class="text-primary text-decoration-none">Analytics</a></div>
                <div class="cst-stat-sub text-muted"><a href="{{ route('reports.project-summary') }}" class="text-secondary text-decoration-none">Financial Summaries &rarr;</a></div>
            </div>
        </div>
    </div>

    {{-- Projects Section --}}
    <div class="cst-card mb-4">
        <div class="cst-card-header d-flex justify-content-between align-items-center">
            <h5 class="cst-card-title mb-0">Active & Recent Projects</h5>
            <a href="{{ route('projects.index') }}" class="btn btn-sm btn-outline-primary">View All Projects</a>
        </div>
        <div class="cst-card-body p-0">
            @if($projects->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="ti ti-building-factory-2 fs-1 d-block mb-2 opacity-50"></i>
                    <p class="mb-2">No projects created yet.</p>
                    <a href="{{ route('projects.create') }}" class="btn btn-sm btn-primary">Create Your First Project</a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Project</th>
                                <th>Location</th>
                                <th>Status</th>
                                <th>Contractors</th>
                                <th>Direct Expenses</th>
                                <th>Workers</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($projects as $project)
                                <tr>
                                    <td>
                                        <a href="{{ route('projects.show', $project) }}" class="fw-semibold text-dark text-decoration-none">
                                            {{ $project->name }}
                                        </a>
                                        @if($project->site_name)
                                            <div class="text-muted small">{{ $project->site_name }}</div>
                                        @endif
                                    </td>
                                    <td>{{ $project->location ?? '—' }}</td>
                                    <td>
                                        <span class="badge {{ $project->status->badgeClass() }}">{{ $project->status->label() }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">{{ $project->contractors->count() }}</span>
                                    </td>
                                    <td class="fw-semibold">
                                        PKR {{ number_format($project->expenses_sum_amount ?? $project->totalExpenses(), 0) }}
                                    </td>
                                    <td>{{ $project->workers_count }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('projects.show', $project) }}" class="btn btn-sm btn-outline-primary">
                                            Manage Hub &rarr;
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

@elseif($user->isContractor())
    {{-- Contractor View --}}
    <div class="row g-4 mb-4">
        <div class="col-sm-6 col-xl-4">
            <div class="cst-card cst-stat-card">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="cst-stat-label">My Projects</span>
                    <div class="cst-stat-icon tone-primary">
                        <i class="ti ti-building-skyscraper"></i>
                    </div>
                </div>
                <div class="cst-stat-value">{{ $projects->count() }}</div>
                <div class="cst-stat-sub text-muted">Assigned Sites</div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-4">
            <div class="cst-card cst-stat-card">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="cst-stat-label">Total Workers</span>
                    <div class="cst-stat-icon tone-info">
                        <i class="ti ti-users"></i>
                    </div>
                </div>
                <div class="cst-stat-value">{{ $totalWorkers }}</div>
                <div class="cst-stat-sub">
                    <span class="badge bg-label-success">{{ $activeWorkers }} Active</span>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-4">
            <div class="cst-card cst-stat-card">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="cst-stat-label">Quick Actions</span>
                    <div class="cst-stat-icon tone-warning">
                        <i class="ti ti-bolt"></i>
                    </div>
                </div>
                <div class="cst-stat-value"><a href="{{ route('attendance.index') }}" class="btn btn-sm btn-warning text-dark">Mark Today's Attendance</a></div>
                <div class="cst-stat-sub"><a href="{{ route('workers.create') }}" class="text-secondary small">+ Add New Worker</a></div>
            </div>
        </div>
    </div>

    {{-- Contractor Projects --}}
    <div class="cst-card mb-4">
        <div class="cst-card-header">
            <h5 class="cst-card-title mb-0">My Assigned Projects</h5>
        </div>
        <div class="cst-card-body p-0">
            @if($projects->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="ti ti-clipboard-off fs-1 d-block mb-2 opacity-50"></i>
                    <p>No projects currently assigned.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Project</th>
                                <th>Location</th>
                                <th>Contract Amount</th>
                                <th>Workers</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($projects as $proj)
                                <tr>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $proj->name }}</div>
                                        <div class="small text-muted">{{ $proj->site_name }}</div>
                                    </td>
                                    <td>{{ $proj->location ?? '—' }}</td>
                                    <td class="fw-bold text-success">
                                        PKR {{ number_format($proj->pivot?->contract_amount ?? 0, 0) }}
                                    </td>
                                    <td>{{ $proj->workers_count }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('attendance.index', ['project_id' => $proj->id]) }}" class="btn btn-sm btn-warning text-dark">
                                            <i class="ti ti-calendar-check me-1"></i> Attendance
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endif

@endsection
