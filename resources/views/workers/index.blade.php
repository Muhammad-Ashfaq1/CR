@extends('layouts.app')

@section('title', 'Workforce Roster — ' . config('app.name'))

@section('content')
<div class="awt-glass-card awt-tone-primary mb-4">
    <div class="awt-glass-intro">
        <div class="awt-glass-intro-copy">
            <h4 class="awt-glass-intro-title mb-1">
                <i class="icon-base ti tabler-hammer me-2 text-primary"></i> Workforce Directory
            </h4>
            <p class="awt-glass-intro-subtitle mb-0">Track registered site workers, trade skills, daily wage rates, and contractor links.</p>
        </div>
        <div class="awt-glass-intro-actions">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createWorkerModal">
                <i class="icon-base ti tabler-user-plus me-1"></i> Register Worker
            </button>
        </div>
    </div>
</div>

<div class="awt-table-card awt-tone-secondary mb-4">
    <div class="awt-listing-filter-strip">
        <form method="GET" action="{{ route('workers.index') }}" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Search</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="icon-base ti tabler-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Name or phone..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Contractor</label>
                <select name="contractor_id" class="form-select">
                    <option value="">All Contractors</option>
                    @foreach($contractors as $c)
                        <option value="{{ $c->id }}" {{ request('contractor_id') == $c->id ? 'selected' : '' }}>
                            {{ $c->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold">Trade / Skill</label>
                <select name="worker_type" class="form-select">
                    <option value="">All Trades</option>
                    @foreach($workerTypes as $type)
                        <option value="{{ $type }}" {{ request('worker_type') == $type ? 'selected' : '' }}>
                            {{ $type }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="icon-base ti tabler-filter me-1"></i> Filter
                </button>
                <a href="{{ route('workers.index') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Worker Name</th>
                    <th>Trade Skill</th>
                    <th>Contractor</th>
                    <th>Assigned Project</th>
                    <th>Daily Wage</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($workers as $worker)
                    <tr>
                        <td>
                            <a href="{{ route('workers.show', $worker) }}" class="fw-semibold text-primary text-decoration-none d-block">
                                {{ $worker->name }}
                            </a>
                            <div class="small text-muted">{{ $worker->phone ?? 'No phone' }}</div>
                        </td>
                        <td>
                            <span class="badge bg-label-info">{{ $worker->worker_type }}</span>
                        </td>
                        <td>
                            <a href="{{ route('contractors.show', $worker->contractor) }}" class="text-dark text-decoration-none">
                                {{ $worker->contractor?->name }}
                            </a>
                        </td>
                        <td>{{ $worker->project?->name ?? 'General / Any Site' }}</td>
                        <td class="fw-bold text-heading">
                            PKR {{ number_format($worker->daily_wage, 0) }}/day
                        </td>
                        <td>
                            <span class="badge {{ $worker->status === 'active' ? 'bg-label-success' : 'bg-label-secondary' }}">
                                {{ ucfirst($worker->status) }}
                            </span>
                        </td>
                        <td class="text-end">
                            <div class="d-inline-flex gap-1 align-items-center">
                                <a href="{{ route('workers.show', $worker) }}" class="btn btn-sm btn-outline-primary">
                                    Profile &rarr;
                                </a>
                                <button type="button" class="btn btn-sm btn-icon btn-text-secondary rounded-pill" data-bs-toggle="modal" data-bs-target="#editWorkerModal_{{ $worker->id }}" title="Edit / Revise Wage">
                                    <i class="icon-base ti tabler-edit"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <div class="awt-empty-state">
                                <span class="awt-empty-state-icon">
                                    <i class="icon-base ti tabler-hammer"></i>
                                </span>
                                <h6 class="awt-empty-state-title">No workers found</h6>
                                <p class="awt-empty-state-desc">No site worker records matched your search filters.</p>
                                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#createWorkerModal">
                                    <i class="icon-base ti tabler-user-plus me-1"></i> Register Worker
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($workers->hasPages())
        <div class="card-footer">
            {{ $workers->links() }}
        </div>
    @endif
</div>

{{-- Register Worker Modal --}}
<div class="modal fade" id="createWorkerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white"><i class="icon-base ti tabler-user-plus me-2"></i> Register New Worker</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('workers.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label required fw-semibold">Contractor (Employer)</label>
                            <select name="contractor_id" class="form-select" required>
                                <option value="">Select Contractor...</option>
                                @foreach($contractors as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->company_name ?? 'Individual' }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Default Assigned Project</label>
                            <select name="project_id" class="form-select">
                                <option value="">Any Project / Site Pool</option>
                                @foreach($projects as $p)
                                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required fw-semibold">Worker Full Name</label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Muhammad Rasheed" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Phone Number</label>
                            <input type="text" name="phone" class="form-control" placeholder="03xx-xxxxxxx">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required fw-semibold">Trade / Skill Category</label>
                            <select name="worker_type" class="form-select" required>
                                @foreach($workerTypes as $type)
                                    <option value="{{ $type }}" {{ $type === 'Laborer' ? 'selected' : '' }}>{{ $type }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required fw-semibold">Initial Daily Wage (PKR)</label>
                            <input type="number" step="0.01" name="daily_wage" class="form-control fs-5 fw-bold" value="1500" placeholder="e.g. 1500" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Joining Date</label>
                            <input type="date" name="joining_date" class="form-control" value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required fw-semibold">Status</label>
                            <select name="status" class="form-select" required>
                                <option value="active" selected>Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Notes / Additional Info</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Experience, emergency contact..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="icon-base ti tabler-check me-1"></i> Register Worker</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Worker Modals --}}
@foreach($workers as $worker)
<div class="modal fade" id="editWorkerModal_{{ $worker->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white"><i class="icon-base ti tabler-edit me-2"></i> Edit Worker: {{ $worker->name }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('workers.update', $worker) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label required fw-semibold">Contractor (Employer)</label>
                            <select name="contractor_id" class="form-select" required>
                                @foreach($contractors as $c)
                                    <option value="{{ $c->id }}" {{ $worker->contractor_id == $c->id ? 'selected' : '' }}>
                                        {{ $c->name }} ({{ $c->company_name ?? 'Individual' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Default Assigned Project</label>
                            <select name="project_id" class="form-select">
                                <option value="">Any Project / Site Pool</option>
                                @foreach($projects as $p)
                                    <option value="{{ $p->id }}" {{ $worker->project_id == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required fw-semibold">Worker Full Name</label>
                            <input type="text" name="name" class="form-control" value="{{ $worker->name }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Phone Number</label>
                            <input type="text" name="phone" class="form-control" value="{{ $worker->phone }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required fw-semibold">Trade / Skill Category</label>
                            <select name="worker_type" class="form-select" required>
                                @foreach($workerTypes as $type)
                                    <option value="{{ $type }}" {{ $worker->worker_type === $type ? 'selected' : '' }}>{{ $type }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required fw-semibold">Daily Wage Rate (PKR)</label>
                            <input type="number" step="0.01" name="daily_wage" class="form-control fs-5 fw-bold" value="{{ $worker->daily_wage }}" required>
                        </div>
                        <div class="col-12 p-3 bg-light rounded border">
                            <h6 class="fw-bold mb-2 text-primary"><i class="icon-base ti tabler-history me-1"></i> Wage Revision Details (If changing rate)</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Effective From Date</label>
                                    <input type="date" name="wage_effective_date" class="form-control" value="{{ date('Y-m-d') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Reason for Change</label>
                                    <input type="text" name="wage_change_reason" class="form-control" placeholder="e.g. Rate revision, seniority promotion...">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Joining Date</label>
                            <input type="date" name="joining_date" class="form-control" value="{{ $worker->joining_date?->format('Y-m-d') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required fw-semibold">Status</label>
                            <select name="status" class="form-select" required>
                                <option value="active" {{ $worker->status === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ $worker->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Notes / Additional Info</label>
                            <textarea name="notes" class="form-control" rows="2">{{ $worker->notes }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-danger" onclick="confirmDelete(() => document.getElementById('delWorkForm_{{ $worker->id }}').submit(), 'Are you sure you want to delete this worker?')">
                        <i class="icon-base ti tabler-trash me-1"></i> Delete
                    </button>
                    <div>
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary"><i class="icon-base ti tabler-device-floppy me-1"></i> Save Changes</button>
                    </div>
                </div>
            </form>
            <form id="delWorkForm_{{ $worker->id }}" action="{{ route('workers.destroy', $worker) }}" method="POST" class="d-none">
                @csrf
                @method('DELETE')
            </form>
        </div>
    </div>
</div>
@endforeach

@push('scripts')
@if(request('action') === 'create')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const m = document.getElementById('createWorkerModal');
        if (m) new bootstrap.Modal(m).show();
    });
</script>
@elseif(request('edit'))
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const m = document.getElementById('editWorkerModal_{{ request('edit') }}');
        if (m) new bootstrap.Modal(m).show();
    });
</script>
@endif
@endpush
@endsection
