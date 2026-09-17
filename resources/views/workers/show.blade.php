@extends('layouts.app')

@section('title', $worker->name . ' — Worker Profile — ' . config('app.name'))

@section('content')
<div class="cst-page-header">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <h1 class="cst-page-title mb-0">{{ $worker->name }}</h1>
            <span class="badge {{ $worker->status === 'active' ? 'bg-label-success' : 'bg-label-secondary' }}">
                {{ ucfirst($worker->status) }}
            </span>
            <span class="badge bg-light text-dark border">{{ $worker->worker_type }}</span>
        </div>
        <p class="cst-page-subtitle">
            Contractor: <strong>{{ $worker->contractor?->name }}</strong> &bull;
            Phone: {{ $worker->phone ?? '—' }} &bull;
            Current Wage: <span class="fw-bold text-dark">PKR {{ number_format($worker->daily_wage, 0) }}/day</span>
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('workers.edit', $worker) }}" class="btn btn-primary">
            <i class="ti ti-edit me-1"></i> Edit / Update Wage
        </a>
        <a href="{{ route('workers.index') }}" class="btn btn-outline-secondary">
            &larr; All Workers
        </a>
    </div>
</div>

{{-- Attendance & Earning Summary --}}
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="cst-card cst-stat-card">
            <div class="cst-stat-label">Total Days Worked</div>
            <div class="cst-stat-value">{{ $attendanceSummary['total'] }}</div>
            <div class="cst-stat-sub text-muted">Attendance records</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="cst-card cst-stat-card">
            <div class="cst-stat-label">Full Days / Half Days</div>
            <div class="cst-stat-value text-success">{{ $attendanceSummary['full_day'] }} <span class="fs-6 text-muted">/ {{ $attendanceSummary['half_day'] }}</span></div>
            <div class="cst-stat-sub text-muted">{{ $attendanceSummary['absent'] }} absent &bull; {{ $attendanceSummary['leave'] }} leave</div>
        </div>
    </div>
    <div class="col-sm-12 col-xl-6">
        <div class="cst-card cst-stat-card border-success">
            <div class="cst-stat-label text-success fw-bold">Total Wages Payable / Earned</div>
            <div class="cst-stat-value fs-4 text-success fw-bold">PKR {{ number_format($attendanceSummary['total_pay'], 0) }}</div>
            <div class="cst-stat-sub text-muted">Calculated from wage rate at time of each attendance</div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    {{-- Attendance Log --}}
    <div class="col-lg-7">
        <div class="cst-card mb-4">
            <div class="cst-card-header d-flex justify-content-between align-items-center">
                <h5 class="cst-card-title mb-0">Recent Attendance History</h5>
                <a href="{{ route('attendance.history', ['worker_id' => $worker->id]) }}" class="btn btn-sm btn-outline-primary">Full History</a>
            </div>
            <div class="cst-card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Project</th>
                                <th>Status</th>
                                <th>Wage at Time</th>
                                <th class="text-end">Payable</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($worker->attendance as $att)
                                <tr>
                                    <td>{{ $att->attendance_date->format('d M Y') }}</td>
                                    <td>{{ $att->project?->name }}</td>
                                    <td>
                                        <span class="badge {{ $att->status->badgeClass() }}">
                                            <i class="ti {{ $att->status->icon() }} me-1"></i>{{ $att->status->label() }}
                                        </span>
                                    </td>
                                    <td>PKR {{ number_format($att->wage_at_time, 0) }}</td>
                                    <td class="text-end fw-semibold text-success">PKR {{ number_format($att->payable_amount, 0) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">No attendance recorded yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Wage History Timeline --}}
    <div class="col-lg-5">
        <div class="cst-card">
            <div class="cst-card-header d-flex justify-content-between align-items-center">
                <h5 class="cst-card-title mb-0">Wage Revision History</h5>
                <a href="{{ route('workers.edit', $worker) }}" class="btn btn-sm btn-outline-secondary">Revise Wage</a>
            </div>
            <div class="cst-card-body p-3">
                <div class="list-group list-group-flush">
                    @forelse($worker->wageHistory as $history)
                        <div class="list-group-item px-0 py-3 border-bottom">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-bold fs-6 text-dark">PKR {{ number_format($history->daily_wage, 0) }}/day</span>
                                <span class="badge bg-light text-dark border">Effective: {{ $history->effective_from->format('d M Y') }}</span>
                            </div>
                            <div class="small text-muted">{{ $history->reason ?? 'Rate revision' }}</div>
                            <div class="small text-muted" style="font-size: 0.75rem;">Recorded by {{ $history->changedBy?->name ?? 'System' }}</div>
                        </div>
                    @empty
                        <div class="text-center py-3 text-muted">Initial wage only.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
