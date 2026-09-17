@extends('layouts.app')

@section('title', 'Projects — ' . config('app.name'))

@section('content')
<div class="awt-glass-card awt-tone-primary mb-4">
    <div class="awt-glass-intro">
        <div class="awt-glass-intro-copy">
            <h4 class="awt-glass-intro-title mb-1">
                <i class="icon-base ti tabler-building-skyscraper me-2 text-primary"></i> Construction Projects
            </h4>
            <p class="awt-glass-intro-subtitle mb-0">Track active sites, overall budgets, contractor commitments, workforce, and completion schedules.</p>
        </div>
        <div class="awt-glass-intro-actions">
            @if(auth()->user()->isOwner() || auth()->user()->isAdmin())
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createProjectModal">
                    <i class="icon-base ti tabler-plus me-1"></i> New Project
                </button>
            @endif
        </div>
    </div>
</div>

<div class="awt-table-card awt-tone-secondary mb-4">
    {{-- Search & Filter Toolbar --}}
    <div class="awt-listing-filter-strip">
        <form method="GET" action="{{ route('projects.index') }}" class="row g-3 align-items-end">
            <div class="col-md-5">
                <label class="form-label small fw-semibold">Search Projects</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="icon-base ti tabler-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Project name, site name, or location..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Status Filter</label>
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
                <button type="submit" class="btn btn-primary w-100">
                    <i class="icon-base ti tabler-filter me-1"></i> Filter
                </button>
                <a href="{{ route('projects.index') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
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
                            <a href="{{ route('projects.show', $project) }}" class="fw-semibold text-primary text-decoration-none d-block">
                                {{ $project->name }}
                            </a>
                            <div class="small text-muted">
                                <i class="icon-base ti tabler-map-pin me-1"></i>{{ $project->location ?? ($project->site_name ?? 'Location not specified') }}
                            </div>
                        </td>
                        <td>{{ $project->owner?->name ?? '—' }}</td>
                        <td>
                            @php
                                $badgeClass = match($project->status->value ?? $project->status) {
                                    'planning' => 'bg-label-info',
                                    'in_progress', 'active' => 'bg-label-success',
                                    'on_hold' => 'bg-label-warning',
                                    'completed' => 'bg-label-primary',
                                    'cancelled' => 'bg-label-danger',
                                    default => 'bg-label-secondary'
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }}">{{ $project->status->label() }}</span>
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
                            <span class="badge bg-label-info">{{ $project->contractors->count() }} active</span>
                        </td>
                        <td class="fw-semibold text-heading">
                            PKR {{ number_format($project->totalExpenses(), 0) }}
                        </td>
                        <td>
                            <span class="badge bg-label-secondary">{{ $project->workers_count }}</span>
                        </td>
                        <td class="text-end">
                            <div class="d-inline-flex gap-1 align-items-center">
                                <a href="{{ route('projects.show', $project) }}" class="btn btn-sm btn-outline-primary">
                                    Open Hub &rarr;
                                </a>
                                @if(auth()->user()->isAdmin() || auth()->user()->isOwner())
                                    <button type="button" class="btn btn-sm btn-icon btn-text-secondary rounded-pill" data-bs-toggle="modal" data-bs-target="#editProjectModal_{{ $project->id }}" title="Edit Project">
                                        <i class="icon-base ti tabler-edit"></i>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">
                            <div class="awt-empty-state">
                                <span class="awt-empty-state-icon">
                                    <i class="icon-base ti tabler-building-factory-2"></i>
                                </span>
                                <h6 class="awt-empty-state-title">No construction projects found</h6>
                                <p class="awt-empty-state-desc">No projects matching your search criteria were located.</p>
                                @if(auth()->user()->isOwner() || auth()->user()->isAdmin())
                                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#createProjectModal">
                                        <i class="icon-base ti tabler-plus me-1"></i> Create New Project
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($projects->hasPages())
        <div class="card-footer">
            {{ $projects->links() }}
        </div>
    @endif
</div>

{{-- Create Project Modal --}}
@if(auth()->user()->isOwner() || auth()->user()->isAdmin())
<div class="modal fade" id="createProjectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white"><i class="icon-base ti tabler-building-plus me-2"></i> Create Construction Project</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('projects.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label required fw-semibold">Project Name</label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Gulberg Commercial Plaza" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Site / Plot Identifier</label>
                            <input type="text" name="site_name" class="form-control" placeholder="e.g. Plot 42-B, Block D">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Location / City</label>
                            <input type="text" name="location" class="form-control" placeholder="e.g. Main Boulevard, Lahore">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required fw-semibold">Status</label>
                            <select name="status" class="form-select" required>
                                @foreach($statuses as $status)
                                    <option value="{{ $status->value }}" {{ $status->value === 'active' ? 'selected' : '' }}>
                                        {{ $status->label() }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @if(auth()->user()->isAdmin())
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Project Owner</label>
                                <select name="owner_id" class="form-select">
                                    <option value="">Select Owner</option>
                                    @foreach($owners as $owner)
                                        <option value="{{ $owner->id }}">{{ $owner->name }} ({{ $owner->email }})</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Start Date</label>
                            <input type="date" name="start_date" class="form-control" value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Expected Completion</label>
                            <input type="date" name="expected_completion_date" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Project Description / Notes</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Scope of work, key deliverables..."></textarea>
                        </div>

                        <div class="col-12 pt-2 border-top">
                            <h6 class="fw-bold text-primary mb-2"><i class="icon-base ti tabler-user-cog me-1"></i> Initial Contractor Assignment (Optional)</h6>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Contractor</label>
                            <select name="contractor_id" class="form-select">
                                <option value="">None / Assign Later</option>
                                @foreach($contractors as $contractor)
                                    <option value="{{ $contractor->id }}">{{ $contractor->name }} {{ $contractor->company_name ? "({$contractor->company_name})" : '' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Agreed Contract Amount (PKR)</label>
                            <input type="number" step="0.01" name="contract_amount" class="form-control" placeholder="e.g. 5000000">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="icon-base ti tabler-check me-1"></i> Create Project</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Project Modals for Each Row --}}
@foreach($projects as $project)
<div class="modal fade" id="editProjectModal_{{ $project->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white"><i class="icon-base ti tabler-edit me-2"></i> Edit Project: {{ $project->name }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('projects.update', $project) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label required fw-semibold">Project Name</label>
                            <input type="text" name="name" class="form-control" value="{{ $project->name }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Site / Plot Identifier</label>
                            <input type="text" name="site_name" class="form-control" value="{{ $project->site_name }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Location / City</label>
                            <input type="text" name="location" class="form-control" value="{{ $project->location }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required fw-semibold">Status</label>
                            <select name="status" class="form-select" required>
                                @foreach($statuses as $status)
                                    <option value="{{ $status->value }}" {{ $project->status->value === $status->value ? 'selected' : '' }}>
                                        {{ $status->label() }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @if(auth()->user()->isAdmin())
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Project Owner</label>
                                <select name="owner_id" class="form-select">
                                    @foreach($owners as $owner)
                                        <option value="{{ $owner->id }}" {{ $project->owner_id == $owner->id ? 'selected' : '' }}>{{ $owner->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Start Date</label>
                            <input type="date" name="start_date" class="form-control" value="{{ $project->start_date?->format('Y-m-d') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Expected Completion</label>
                            <input type="date" name="expected_completion_date" class="form-control" value="{{ $project->expected_completion_date?->format('Y-m-d') }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Project Description / Notes</label>
                            <textarea name="notes" class="form-control" rows="2">{{ $project->notes }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-danger" onclick="confirmDelete(() => document.getElementById('deleteProjForm_{{ $project->id }}').submit(), 'Are you sure you want to delete this project?')">
                        <i class="icon-base ti tabler-trash me-1"></i> Delete
                    </button>
                    <div>
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary"><i class="icon-base ti tabler-device-floppy me-1"></i> Save Changes</button>
                    </div>
                </div>
            </form>
            <form id="deleteProjForm_{{ $project->id }}" action="{{ route('projects.destroy', $project) }}" method="POST" class="d-none">
                @csrf
                @method('DELETE')
            </form>
        </div>
    </div>
</div>
@endforeach
@endif

@push('scripts')
@if(request('action') === 'create')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const m = document.getElementById('createProjectModal');
        if (m) new bootstrap.Modal(m).show();
    });
</script>
@elseif(request('edit'))
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const m = document.getElementById('editProjectModal_{{ request('edit') }}');
        if (m) new bootstrap.Modal(m).show();
    });
</script>
@endif
@endpush
@endsection
