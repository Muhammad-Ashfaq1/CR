@extends('layouts.app')

@section('title', 'Activity Audit Trail — ' . config('app.name'))

@section('content')
<div class="awt-glass-card awt-tone-primary mb-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="avatar avatar-lg rounded-3 bg-label-primary d-flex align-items-center justify-content-center">
                <i class="icon-base ti tabler-activity fs-2"></i>
            </div>
            <div>
                <h4 class="awt-dash-title mb-1">Activity Audit Trail</h4>
                <p class="awt-dash-subtitle mb-0">Complete chronological log of project creations, contractor assignments, payments, wages, and expenses.</p>
            </div>
        </div>
    </div>
</div>

<div class="awt-listing-filter-strip awt-tone-secondary mb-4">
    <form method="GET" action="{{ route('activities.index') }}" class="row g-3 align-items-end">
        <div class="col-md-3">
            <label class="form-label small fw-semibold text-muted text-uppercase">Project</label>
            <select name="project_id" class="form-select">
                <option value="">All Projects</option>
                @foreach($projects as $p)
                    <option value="{{ $p->id }}" {{ request('project_id') == $p->id ? 'selected' : '' }}>
                        {{ $p->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small fw-semibold text-muted text-uppercase">User</label>
            <select name="user_id" class="form-select">
                <option value="">All Users</option>
                @foreach($users as $u)
                    <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>
                        {{ $u->name }} ({{ $u->role->label() }})
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-semibold text-muted text-uppercase">Event Type</label>
            <select name="event" class="form-select">
                <option value="">All Events</option>
                @foreach($events as $ev)
                    <option value="{{ $ev }}" {{ request('event') == $ev ? 'selected' : '' }}>
                        {{ str_replace('_', ' ', Str::title($ev)) }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-semibold text-muted text-uppercase">From Date</label>
            <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
        </div>
        <div class="col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-primary w-100">
                <i class="icon-base ti tabler-filter me-1"></i> Filter
            </button>
            <a href="{{ route('activities.index') }}" class="btn btn-outline-secondary">Reset</a>
        </div>
    </form>
</div>

<div class="card awt-table-card awt-tone-secondary">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th class="text-uppercase small fw-semibold text-muted">Time</th>
                    <th class="text-uppercase small fw-semibold text-muted">User</th>
                    <th class="text-uppercase small fw-semibold text-muted">Event</th>
                    <th class="text-uppercase small fw-semibold text-muted">Project</th>
                    <th class="text-uppercase small fw-semibold text-muted">Description / Details</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td>
                            <div class="fw-semibold text-heading">{{ $log->created_at->format('d M Y') }}</div>
                            <div class="small text-muted">{{ $log->created_at->format('h:i A') }} ({{ $log->created_at->diffForHumans() }})</div>
                        </td>
                        <td>
                            <div class="fw-semibold text-heading">{{ $log->user?->name ?? 'System' }}</div>
                            <div class="small text-muted">{{ $log->user?->role?->label() }}</div>
                        </td>
                        <td>
                            <span class="badge bg-label-info">{{ str_replace('_', ' ', Str::title($log->event)) }}</span>
                        </td>
                        <td>
                            @if($log->project)
                                <a href="{{ route('projects.show', $log->project) }}" class="text-primary text-decoration-none fw-semibold">
                                    {{ $log->project->name }}
                                </a>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            <div class="text-body">{{ $log->description ?? 'No description' }}</div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="p-0">
                            <div class="awt-empty-state text-center py-5">
                                <div class="awt-empty-state-icon text-muted mb-2">
                                    <i class="icon-base ti tabler-activity-heartbeat fs-1"></i>
                                </div>
                                <h6 class="fw-semibold mb-1">No activity logs found</h6>
                                <p class="text-muted small mb-0">System events and user actions will be recorded here.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($logs->hasPages())
        <div class="card-footer d-flex flex-wrap justify-content-between align-items-center">
            <div class="small text-muted">Showing {{ $logs->firstItem() }} to {{ $logs->lastItem() }} of {{ $logs->total() }} entries</div>
            <div>{{ $logs->links() }}</div>
        </div>
    @endif
</div>
@endsection
