@extends('layouts.app')

@section('title', 'Attendance History — ' . config('app.name'))

@section('content')
<div class="cst-page-header">
    <div>
        <h1 class="cst-page-title">Attendance History & Audit Log</h1>
        <p class="cst-page-subtitle">Filter attendance records, view daily wages calculated at the time, and audit labor cost.</p>
    </div>
    <div>
        <a href="{{ route('attendance.index') }}" class="btn btn-warning text-dark">
            <i class="ti ti-calendar-plus me-1"></i> Today's Sheet
        </a>
    </div>
</div>

<div class="cst-card mb-4">
    <div class="cst-card-body p-3">
        <form method="GET" action="{{ route('attendance.history') }}" class="row g-3 align-items-end">
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
                <label class="form-label small">Worker</label>
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
                <label class="form-label small">Status</label>
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
                <label class="form-label small">From Date</label>
                <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
                <a href="{{ route('attendance.history') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

{{-- Summary Ribbon --}}
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="cst-card cst-stat-card">
            <div class="cst-stat-label">Full Day Shifts</div>
            <div class="cst-stat-value text-success">{{ $fullDaysCount }}</div>
            <div class="cst-stat-sub text-muted">100% Wage</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="cst-card cst-stat-card">
            <div class="cst-stat-label">Half Day Shifts</div>
            <div class="cst-stat-value text-warning">{{ $halfDaysCount }}</div>
            <div class="cst-stat-sub text-muted">50% Wage</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="cst-card cst-stat-card">
            <div class="cst-stat-label">Absences</div>
            <div class="cst-stat-value text-danger">{{ $absentCount }}</div>
            <div class="cst-stat-sub text-muted">0% Wage</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="cst-card cst-stat-card border-success">
            <div class="cst-stat-label text-success fw-bold">Total Wages Payable</div>
            <div class="cst-stat-value text-success fs-4 fw-bold">PKR {{ number_format($totalWages, 0) }}</div>
            <div class="cst-stat-sub text-muted">Filtered Range</div>
        </div>
    </div>
</div>

<div class="cst-card">
    <div class="cst-card-body p-0">
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
                                <a href="{{ route('workers.show', $rec->worker) }}" class="fw-semibold text-dark text-decoration-none">
                                    {{ $rec->worker?->name }}
                                </a>
                                <div class="small text-muted">{{ $rec->worker?->worker_type }} &bull; {{ $rec->worker?->contractor?->name }}</div>
                            </td>
                            <td>{{ $rec->project?->name }}</td>
                            <td>
                                <span class="badge {{ $rec->status->badgeClass() }}">
                                    <i class="ti {{ $rec->status->icon() }} me-1"></i>{{ $rec->status->label() }}
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
                                    <button type="submit" class="btn btn-sm btn-icon btn-outline-danger" title="Delete">
                                        <i class="ti ti-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="ti ti-calendar-off fs-1 d-block mb-2 opacity-50"></i>
                                <p class="mb-2">No attendance records match your filter.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($records->hasPages())
        <div class="cst-card-footer p-3">
            {{ $records->links() }}
        </div>
    @endif
</div>
@endsection
