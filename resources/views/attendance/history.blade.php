@extends('layouts.app')

@section('title', 'Attendance History — ' . config('app.name'))

@section('content')
<div class="pos-glass-intro pos-tone-primary mb-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <h4 class="pos-glass-intro-title mb-1">
                <i class="icon-base ti tabler-history me-2 text-primary"></i> Attendance History & Audit Log
            </h4>
            <div class="pos-glass-intro-sub">Filter attendance records, view daily wages calculated at the time, and audit labor cost.</div>
        </div>
        <div>
            <a href="{{ route('attendance.index') }}" class="btn btn-warning text-dark fw-semibold">
                <i class="icon-base ti tabler-calendar-plus me-1"></i> Today's Grid Sheet
            </a>
        </div>
    </div>
</div>

<div class="pos-listing-panel mb-4">
    <form method="GET" action="{{ route('attendance.history') }}" class="row g-3 align-items-end">
        <div class="col-md-3">
            <label class="form-label small fw-semibold">Project</label>
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
            <label class="form-label small fw-semibold">Worker</label>
            <select name="worker_id" class="form-select">
                <option value="">All Workers</option>
                @foreach($workers as $w)
                    <option value="{{ $w->id }}" {{ request('worker_id') == $w->id ? 'selected' : '' }}>
                        {{ $w->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-semibold">Status</label>
            <select name="status" class="form-select">
                <option value="">All Statuses</option>
                @foreach($statuses as $st)
                    <option value="{{ $st->value }}" {{ request('status') === $st->value ? 'selected' : '' }}>
                        {{ $st->label() }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-semibold">From Date</label>
            <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
        </div>
        <div class="col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-primary w-100">
                <i class="icon-base ti tabler-filter me-1"></i> Filter
            </button>
            <a href="{{ route('attendance.history') }}" class="btn btn-outline-secondary">Reset</a>
        </div>
    </form>
</div>

{{-- Summary Glass Ribbon --}}
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="pos-glass-card pos-tone-success">
            <div class="pos-stat-body">
                <div class="pos-stat-head">
                    <span class="pos-stat-label">Full Day Shifts</span>
                    <span class="pos-stat-icon"><i class="icon-base ti tabler-clock-check"></i></span>
                </div>
                <div class="pos-stat-value text-success">{{ $fullDaysCount }}</div>
                <div class="pos-stat-sub text-muted">100% Rate Multiplier</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="pos-glass-card pos-tone-warning">
            <div class="pos-stat-body">
                <div class="pos-stat-head">
                    <span class="pos-stat-label">Half Day Shifts</span>
                    <span class="pos-stat-icon"><i class="icon-base ti tabler-clock-pause"></i></span>
                </div>
                <div class="pos-stat-value text-warning">{{ $halfDaysCount }}</div>
                <div class="pos-stat-sub text-muted">50% Rate Multiplier</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="pos-glass-card pos-tone-danger">
            <div class="pos-stat-body">
                <div class="pos-stat-head">
                    <span class="pos-stat-label">Absences / Unpaid</span>
                    <span class="pos-stat-icon"><i class="icon-base ti tabler-clock-x"></i></span>
                </div>
                <div class="pos-stat-value text-danger">{{ $absentCount }}</div>
                <div class="pos-stat-sub text-muted">0% Rate Multiplier</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="pos-glass-card pos-tone-primary">
            <div class="pos-stat-body">
                <div class="pos-stat-head">
                    <span class="pos-stat-label">Total Wages Payable</span>
                    <span class="pos-stat-icon"><i class="icon-base ti tabler-cash"></i></span>
                </div>
                <div class="pos-stat-value text-primary">PKR {{ number_format($totalWages, 0) }}</div>
                <div class="pos-stat-sub text-muted">Filtered Date Range</div>
            </div>
        </div>
    </div>
</div>

<div class="pos-listing">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Date</th>
                    <th>Worker</th>
                    <th>Project</th>
                    <th>Status</th>
                    <th>Wage at Time</th>
                    <th>Calculated Payable</th>
                    <th>Recorded By</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $rec)
                    <tr>
                        <td>{{ $rec->attendance_date->format('d M Y') }}</td>
                        <td>
                            <a href="{{ route('workers.show', $rec->worker) }}" class="fw-semibold text-primary text-decoration-none d-block">
                                {{ $rec->worker?->name }}
                            </a>
                            <div class="small text-muted">{{ $rec->worker?->worker_type }} &bull; {{ $rec->worker?->contractor?->name }}</div>
                        </td>
                        <td>{{ $rec->project?->name }}</td>
                        <td>
                            <span class="badge {{ $rec->status->badgeClass() }}">
                                {{ $rec->status->label() }}
                            </span>
                        </td>
                        <td>PKR {{ number_format($rec->wage_at_time, 0) }}</td>
                        <td class="fw-bold text-success">
                            PKR {{ number_format($rec->payable_amount, 2) }}
                        </td>
                        <td><div class="small text-muted">{{ $rec->recordedBy?->name ?? 'System' }}</div></td>
                        <td class="text-end">
                            <form action="{{ route('attendance.destroy', $rec) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Delete this attendance record?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                    <i class="icon-base ti tabler-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="icon-base ti tabler-calendar-off fs-1 d-block mb-2 opacity-50"></i>
                            <p class="mb-2">No attendance records match your filter.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($records->hasPages())
        <div class="p-3 border-top">
            {{ $records->links() }}
        </div>
    @endif
</div>
@endsection
