@extends('layouts.app')

@section('title', 'Workers Roster — ' . config('app.name'))

@section('content')
<div class="cst-page-header">
    <div>
        <h1 class="cst-page-title">Workforce Directory</h1>
        <p class="cst-page-subtitle">Track registered site workers, trade skills, daily wage rates, and contractors.</p>
    </div>
    <div>
        <a href="{{ route('workers.create') }}" class="btn btn-primary">
            <i class="ti ti-plus me-1"></i> Register Worker
        </a>
    </div>
</div>

<div class="cst-card mb-4">
    <div class="cst-card-body p-3">
        <form method="GET" action="{{ route('workers.index') }}" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label small">Search</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="ti ti-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Name or phone..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label small">Contractor</label>
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
                <label class="form-label small">Trade / Skill</label>
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
                <label class="form-label small">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
                <a href="{{ route('workers.index') }}" class="btn btn-outline-secondary">Reset</a>
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
                                <a href="{{ route('workers.show', $worker) }}" class="fw-semibold text-dark text-decoration-none">
                                    {{ $worker->name }}
                                </a>
                                <div class="small text-muted">{{ $worker->phone ?? 'No phone' }}</div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">{{ $worker->worker_type }}</span>
                            </td>
                            <td>
                                <a href="{{ route('contractors.show', $worker->contractor) }}" class="text-secondary text-decoration-none">
                                    {{ $worker->contractor?->name }}
                                </a>
                            </td>
                            <td>{{ $worker->project?->name ?? 'Any / General Site' }}</td>
                            <td class="fw-bold text-dark">
                                PKR {{ number_format($worker->daily_wage, 0) }}/day
                            </td>
                            <td>
                                <span class="badge {{ $worker->status === 'active' ? 'bg-label-success' : 'bg-label-secondary' }}">
                                    {{ ucfirst($worker->status) }}
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('workers.show', $worker) }}" class="btn btn-sm btn-outline-primary" title="View Profile">
                                    <i class="ti ti-user"></i> Profile
                                </a>
                                <a href="{{ route('workers.edit', $worker) }}" class="btn btn-sm btn-icon btn-outline-secondary" title="Edit">
                                    <i class="ti ti-edit"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="ti ti-hammer fs-1 d-block mb-2 opacity-50"></i>
                                <p class="mb-2">No workers found.</p>
                                <a href="{{ route('workers.create') }}" class="btn btn-sm btn-primary">Register Worker</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($workers->hasPages())
        <div class="cst-card-footer p-3">
            {{ $workers->links() }}
        </div>
    @endif
</div>
@endsection
