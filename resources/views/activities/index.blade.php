@extends('layouts.app')

@section('title', 'Activity Audit Trail — ' . config('app.name'))

@section('content')
<div class="cst-page-header">
    <div>
        <h1 class="cst-page-title">Activity Audit Trail</h1>
        <p class="cst-page-subtitle">Complete chronological log of project creations, contractor assignments, payments, wages, and expenses.</p>
    </div>
</div>

<div class="cst-card mb-4">
    <div class="cst-card-body p-3">
        <form method="GET" action="{{ route('activities.index') }}" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label small">Project</label>
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
                <label class="form-label small">User</label>
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
                <label class="form-label small">Event Type</label>
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
                <label class="form-label small">From Date</label>
                <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
                <a href="{{ route('activities.index') }}" class="btn btn-outline-secondary">Reset</a>
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
                        <th>Time</th>
                        <th>User</th>
                        <th>Event</th>
                        <th>Project</th>
                        <th>Description / Details</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td>
                                <div class="fw-semibold text-dark">{{ $log->created_at->format('d M Y') }}</div>
                                <div class="small text-muted">{{ $log->created_at->format('h:i A') }} ({{ $log->created_at->diffForHumans() }})</div>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $log->user?->name ?? 'System' }}</div>
                                <div class="small text-muted">{{ $log->user?->role?->label() }}</div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">{{ str_replace('_', ' ', Str::title($log->event)) }}</span>
                            </td>
                            <td>
                                @if($log->project)
                                    <a href="{{ route('projects.show', $log->project) }}" class="text-primary text-decoration-none">
                                        {{ $log->project->name }}
                                    </a>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <div class="text-dark">{{ $log->description ?? 'No description' }}</div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="icon-base ti tabler-activity-heartbeat fs-1 d-block mb-2 opacity-50"></i>
                                <p>No activity logs found.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($logs->hasPages())
        <div class="cst-card-footer p-3">
            {{ $logs->links() }}
        </div>
    @endif
</div>
@endsection
