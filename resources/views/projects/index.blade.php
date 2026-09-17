@extends('layouts.app')

@section('title', 'Projects — ' . config('app.name'))

@section('content')
<div class="cst-page-header">
    <div>
        <h1 class="cst-page-title">Construction Projects</h1>
        <p class="cst-page-subtitle">Track project budgets, contractors, expenses, workforce, and timelines.</p>
    </div>
    <div>
        @if(auth()->user()->isOwner() || auth()->user()->isAdmin())
            <a href="{{ route('projects.create') }}" class="btn btn-primary">
                <i class="ti ti-plus me-1"></i> New Project
            </a>
        @endif
    </div>
</div>

<div class="cst-card mb-4">
    <div class="cst-card-body p-3">
        <form method="GET" action="{{ route('projects.index') }}" class="row g-3 align-items-end">
            <div class="col-md-5">
                <label class="form-label small">Search Project</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="ti ti-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Project name, site name, location..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-4">
                <label class="form-label small">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status->value }}" {{ request('status') === $status->value ? 'selected' : '' }}>
                            {{ $status->label() }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
                <a href="{{ route('projects.index') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="cst-card">
    <div class="cst-card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Project & Site</th>
                        <th>Owner</th>
                        <th>Status</th>
                        <th>Timeline</th>
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
                                <a href="{{ route('projects.show', $project) }}" class="fw-semibold text-dark text-decoration-none d-block">
                                    {{ $project->name }}
                                </a>
                                <div class="small text-muted">
                                    <i class="ti ti-map-pin me-1"></i>{{ $project->location ?? ($project->site_name ?? 'Location not specified') }}
                                </div>
                            </td>
                            <td>{{ $project->owner?->name ?? '—' }}</td>
                            <td>
                                <span class="badge {{ $project->status->badgeClass() }}">{{ $project->status->label() }}</span>
                            </td>
                            <td>
                                <div class="small">
                                    @if($project->start_date)
                                        <div><span class="text-muted">Start:</span> {{ $project->start_date->format('d M Y') }}</div>
                                    @endif
                                    @if($project->expected_completion_date)
                                        <div><span class="text-muted">Target:</span> {{ $project->expected_completion_date->format('d M Y') }}</div>
                                    @endif
                                    @if(!$project->start_date && !$project->expected_completion_date)
                                        <span class="text-muted">—</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">{{ $project->contractors->count() }}</span>
                            </td>
                            <td class="fw-semibold">
                                PKR {{ number_format($project->totalExpenses(), 0) }}
                            </td>
                            <td>{{ $project->workers_count }}</td>
                            <td class="text-end">
                                <a href="{{ route('projects.show', $project) }}" class="btn btn-sm btn-primary">
                                    Open Hub
                                </a>
                                @if(auth()->user()->isAdmin() || auth()->user()->isOwner())
                                    <a href="{{ route('projects.edit', $project) }}" class="btn btn-sm btn-icon btn-outline-secondary" title="Edit">
                                        <i class="ti ti-edit"></i>
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="ti ti-building-factory-2 fs-1 d-block mb-2 opacity-50"></i>
                                <p class="mb-2">No projects found.</p>
                                @if(auth()->user()->isOwner() || auth()->user()->isAdmin())
                                    <a href="{{ route('projects.create') }}" class="btn btn-sm btn-primary">Create New Project</a>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($projects->hasPages())
        <div class="cst-card-footer p-3">
            {{ $projects->links() }}
        </div>
    @endif
</div>
@endsection
