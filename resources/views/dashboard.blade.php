@extends('layouts.app')

@section('title', 'Dashboard — ' . config('app.name'))

@section('content')
@php
    $user = auth()->user();
@endphp

{{-- Banner Intro --}}
<div class="pos-glass-card pos-tone-primary mb-4">
    <div class="pos-glass-intro">
        <div class="pos-glass-intro-icon">
            <i class="icon-base ti tabler-building-skyscraper" aria-hidden="true"></i>
        </div>
        <div class="pos-glass-intro-content">
            <div class="pos-glass-intro-title">
                <h4 class="mb-1 text-heading fw-bold">Welcome back, {{ $user->name }}!</h4>
                <span class="badge bg-label-primary text-uppercase">{{ $user->role->label() }}</span>
            </div>
            <p class="pos-glass-intro-subtitle mb-0">Live construction site telemetry, financial commitments, workforce attendance, and expense metrics.</p>
        </div>
        <div class="pos-glass-intro-actions">
            @if($user->isOwner() || $user->isAdmin())
                <a href="{{ route('projects.create') }}" class="btn btn-primary">
                    <i class="icon-base ti tabler-plus me-1"></i> New Project
                </a>
                <a href="{{ route('expenses.create') }}" class="btn btn-label-secondary">
                    <i class="icon-base ti tabler-receipt me-1"></i> Add Expense
                </a>
            @endif
            <a href="{{ route('attendance.index') }}" class="btn btn-warning">
                <i class="icon-base ti tabler-calendar-check me-1"></i> Attendance
            </a>
        </div>
    </div>
</div>

@if($user->isOwner())
    {{-- KPI Row --}}
    <div class="row g-4 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="pos-glass-card pos-tone-primary h-100">
                <div class="pos-stat-body">
                    <div class="pos-stat-head">
                        <span class="pos-stat-icon"><i class="icon-base ti tabler-building-skyscraper" aria-hidden="true"></i></span>
                        <h6 class="pos-stat-label">Total Projects</h6>
                    </div>
                    <p class="pos-stat-value">{{ $totalProjects }}</p>
                    <div class="pos-stat-sub">
                        <span class="badge bg-label-success">{{ $activeProjects }} Active Sites</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="pos-glass-card pos-tone-warning h-100">
                <div class="pos-stat-body">
                    <div class="pos-stat-head">
                        <span class="pos-stat-icon"><i class="icon-base ti tabler-receipt" aria-hidden="true"></i></span>
                        <h6 class="pos-stat-label">Direct Expenses</h6>
                    </div>
                    <p class="pos-stat-value">PKR {{ number_format($totalExpenses, 0) }}</p>
                    <div class="pos-stat-sub text-muted">Materials & Operations</div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="pos-glass-card pos-tone-info h-100">
                <div class="pos-stat-body">
                    <div class="pos-stat-head">
                        <span class="pos-stat-icon"><i class="icon-base ti tabler-users" aria-hidden="true"></i></span>
                        <h6 class="pos-stat-label">Total Workforce</h6>
                    </div>
                    <p class="pos-stat-value">{{ $totalWorkers }}</p>
                    <div class="pos-stat-sub text-muted">Registered Site Workers</div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="pos-glass-card pos-tone-success h-100">
                <div class="pos-stat-body">
                    <div class="pos-stat-head">
                        <span class="pos-stat-icon"><i class="icon-base ti tabler-chart-pie" aria-hidden="true"></i></span>
                        <h6 class="pos-stat-label">Financial Analytics</h6>
                    </div>
                    <p class="pos-stat-value fs-5">Reports</p>
                    <div class="pos-stat-sub">
                        <a href="{{ route('reports.project-summary') }}" class="text-success fw-medium">Project Summaries &rarr;</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Projects Table (pos-listing) --}}
    <div class="pos-listing mb-4">
        <div class="pos-glass-card pos-tone-secondary pos-listing-panel">
            <div class="pos-listing-toolbar d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <i class="icon-base ti tabler-building-factory-2 text-primary fs-4"></i>
                    <h5 class="pos-listing-title mb-0">Active Construction Projects</h5>
                </div>
                <a href="{{ route('projects.index') }}" class="btn btn-sm btn-outline-primary">View All Projects</a>
            </div>

            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
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
                                <td>{{ $project->location ?? '—' }}</td>
                                <td>
                                    <span class="badge {{ $project->status->badgeClass() }}">{{ $project->status->label() }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-label-info">{{ $project->contractors->count() }} Contractors</span>
                                </td>
                                <td class="fw-semibold text-dark">
                                    PKR {{ number_format($project->totalExpenses(), 0) }}
                                </td>
                                <td>{{ $project->workers_count }}</td>
                                <td class="text-end">
                                    <a href="{{ route('projects.show', $project) }}" class="btn btn-sm btn-outline-primary">
                                        Open Hub &rarr;
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="icon-base ti tabler-building-factory-2 fs-1 d-block mb-2 opacity-50"></i>
                                    <p class="mb-2">No projects created yet.</p>
                                    <a href="{{ route('projects.create') }}" class="btn btn-sm btn-primary">Create Your First Project</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@elseif($user->isContractor())
    {{-- Contractor View --}}
    <div class="row g-4 mb-4">
        <div class="col-sm-6 col-xl-4">
            <div class="pos-glass-card pos-tone-primary h-100">
                <div class="pos-stat-body">
                    <div class="pos-stat-head">
                        <span class="pos-stat-icon"><i class="icon-base ti tabler-building-skyscraper" aria-hidden="true"></i></span>
                        <h6 class="pos-stat-label">Assigned Projects</h6>
                    </div>
                    <p class="pos-stat-value">{{ $projects->count() }}</p>
                    <div class="pos-stat-sub text-muted">Active Work Sites</div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-4">
            <div class="pos-glass-card pos-tone-info h-100">
                <div class="pos-stat-body">
                    <div class="pos-stat-head">
                        <span class="pos-stat-icon"><i class="icon-base ti tabler-users" aria-hidden="true"></i></span>
                        <h6 class="pos-stat-label">My Workforce</h6>
                    </div>
                    <p class="pos-stat-value">{{ $totalWorkers }}</p>
                    <div class="pos-stat-sub">
                        <span class="badge bg-label-success">{{ $activeWorkers }} Active</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-4">
            <div class="pos-glass-card pos-tone-warning h-100">
                <div class="pos-stat-body">
                    <div class="pos-stat-head">
                        <span class="pos-stat-icon"><i class="icon-base ti tabler-bolt" aria-hidden="true"></i></span>
                        <h6 class="pos-stat-label">Quick Actions</h6>
                    </div>
                    <div class="mt-2">
                        <a href="{{ route('attendance.index') }}" class="btn btn-sm btn-warning w-100 mb-2">Mark Today's Attendance</a>
                        <a href="{{ route('workers.create') }}" class="btn btn-sm btn-outline-secondary w-100">+ Add Worker</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Contractor Projects --}}
    <div class="pos-listing mb-4">
        <div class="pos-glass-card pos-tone-secondary pos-listing-panel">
            <div class="pos-listing-toolbar">
                <h5 class="pos-listing-title mb-0">My Assigned Projects</h5>
            </div>
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
                                <td>{{ $proj->workers_count }}</td>
                                <td class="text-end">
                                    <a href="{{ route('attendance.index', ['project_id' => $proj->id]) }}" class="btn btn-sm btn-warning">
                                        <i class="icon-base ti tabler-calendar-check me-1"></i> Attendance
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">No projects assigned.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif

@endsection
