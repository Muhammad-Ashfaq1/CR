@extends('layouts.app')

@section('title', 'Attendance History — ' . config('app.name'))

@section('content')
<div class="awt-glass-card awt-tone-primary mb-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="avatar avatar-lg rounded-3 bg-label-primary d-flex align-items-center justify-content-center">
                <i class="icon-base ti tabler-history fs-2"></i>
            </div>
            <div>
                <h4 class="awt-dash-title mb-1">Attendance History & Audit Log</h4>
                <p class="awt-dash-subtitle mb-0">Filter attendance records, view daily wages calculated at the time, and audit labor cost.</p>
            </div>
        </div>
        <div>
            <a href="{{ route('attendance.index') }}" class="btn btn-warning text-dark fw-semibold">
                <i class="icon-base ti tabler-calendar-plus me-1"></i> Today's Grid Sheet
            </a>
        </div>
    </div>
</div>

<div class="awt-listing-filter-strip awt-tone-secondary mb-4">
    <form method="GET" action="{{ route('attendance.history') }}" class="row g-3 align-items-end">
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
            <label class="form-label small fw-semibold text-muted text-uppercase">Worker</label>
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
            <label class="form-label small fw-semibold text-muted text-uppercase">Status</label>
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
            <label class="form-label small fw-semibold text-muted text-uppercase">From Date</label>
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
        <div class="awt-kpi-card p-3">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="awt-kpi-title">Full Day Shifts</span>
                <div class="avatar avatar-sm bg-label-success rounded-circle d-flex align-items-center justify-content-center">
                    <i class="icon-base ti tabler-clock-check"></i>
                </div>
            </div>
            <div class="awt-kpi-value text-success mb-1">{{ $fullDaysCount }}</div>
            <div class="awt-kpi-footer text-muted">100% Rate Multiplier</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="awt-kpi-card p-3">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="awt-kpi-title">Half Day Shifts</span>
                <div class="avatar avatar-sm bg-label-warning rounded-circle d-flex align-items-center justify-content-center">
                    <i class="icon-base ti tabler-clock-pause"></i>
                </div>
            </div>
            <div class="awt-kpi-value text-warning mb-1">{{ $halfDaysCount }}</div>
            <div class="awt-kpi-footer text-muted">50% Rate Multiplier</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="awt-kpi-card p-3">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="awt-kpi-title">Absences / Unpaid</span>
                <div class="avatar avatar-sm bg-label-danger rounded-circle d-flex align-items-center justify-content-center">
                    <i class="icon-base ti tabler-clock-x"></i>
                </div>
            </div>
            <div class="awt-kpi-value text-danger mb-1">{{ $absentCount }}</div>
            <div class="awt-kpi-footer text-muted">0% Rate Multiplier</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="awt-kpi-card p-3">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="awt-kpi-title">Wages Payable</span>
                <div class="avatar avatar-sm bg-label-primary rounded-circle d-flex align-items-center justify-content-center">
                    <i class="icon-base ti tabler-cash"></i>
                </div>
            </div>
            <div class="awt-kpi-value text-primary mb-1">PKR {{ number_format($totalWages, 0) }}</div>
            <div class="awt-kpi-footer text-muted">Filtered Date Range</div>
        </div>
    </div>
</div>

<div class="awt-table-card awt-tone-secondary">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="border-bottom">
                <tr>
                    <th class="text-uppercase small fw-semibold text-muted ps-3">Date</th>
                    <th class="text-uppercase small fw-semibold text-muted">Worker</th>
                    <th class="text-uppercase small fw-semibold text-muted">Project</th>
                    <th class="text-uppercase small fw-semibold text-muted">Status</th>
                    <th class="text-uppercase small fw-semibold text-muted">Wage at Time</th>
                    <th class="text-uppercase small fw-semibold text-muted">Calculated Payable</th>
                    <th class="text-uppercase small fw-semibold text-muted">Recorded By</th>
                    <th class="text-uppercase small fw-semibold text-muted text-end pe-3">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $rec)
                    <tr>
                        <td class="ps-3">{{ $rec->attendance_date->format('d M Y') }}</td>
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
                        <td class="text-end pe-3">
                            <form id="delAttForm_{{ $rec->id }}" action="{{ route('attendance.destroy', $rec) }}" method="POST" class="d-inline-block">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="btn btn-sm btn-icon btn-text-danger rounded-pill" title="Delete Attendance" onclick="confirmDelete(() => document.getElementById('delAttForm_{{ $rec->id }}').submit(), 'Delete this attendance record?')">
                                    <i class="icon-base ti tabler-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="p-0">
                            <div class="awt-empty-state">
                                <div class="awt-empty-state-icon text-muted mb-2">
                                    <i class="icon-base ti tabler-calendar-off fs-1"></i>
                                </div>
                                <h6 class="fw-semibold mb-1">No attendance records match your filter</h6>
                                <p class="text-muted small mb-0">Try widening your date range or removing filters.</p>
                            </div>
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
