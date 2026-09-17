@extends('layouts.app')

@section('title', $worker->name . ' — Worker Profile — ' . config('app.name'))

@section('content')
<div class="pos-glass-intro pos-tone-primary mb-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <h4 class="pos-glass-intro-title mb-0">{{ $worker->name }}</h4>
                <span class="badge {{ $worker->status === 'active' ? 'bg-label-success' : 'bg-label-secondary' }}">
                    {{ ucfirst($worker->status) }}
                </span>
                <span class="badge bg-label-info">{{ $worker->worker_type }}</span>
            </div>
            <div class="pos-glass-intro-sub">
                Contractor: <strong>{{ $worker->contractor?->name }}</strong> &bull;
                Phone: {{ $worker->phone ?? '—' }} &bull;
                Current Base Rate: <span class="fw-bold text-dark">PKR {{ number_format($worker->daily_wage, 0) }}/day</span>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('workers.edit', $worker) }}" class="btn btn-primary">
                <i class="icon-base ti tabler-edit me-1"></i> Edit / Revise Wage
            </a>
            <a href="{{ route('workers.index') }}" class="btn btn-outline-secondary">
                <i class="icon-base ti tabler-arrow-left me-1"></i> All Workers
            </a>
        </div>
    </div>
</div>

{{-- Attendance & Earning Summary Tiles --}}
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="pos-glass-card pos-tone-primary">
            <div class="pos-stat-body">
                <div class="pos-stat-head">
                    <span class="pos-stat-label">Total Days Worked</span>
                    <span class="pos-stat-icon"><i class="icon-base ti tabler-calendar-event"></i></span>
                </div>
                <div class="pos-stat-value">{{ $attendanceSummary['total'] }}</div>
                <div class="pos-stat-sub text-muted">Attendance records</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="pos-glass-card pos-tone-success">
            <div class="pos-stat-body">
                <div class="pos-stat-head">
                    <span class="pos-stat-label">Full / Half Shifts</span>
                    <span class="pos-stat-icon"><i class="icon-base ti tabler-clock-check"></i></span>
                </div>
                <div class="pos-stat-value text-success">{{ $attendanceSummary['full_day'] }} <span class="fs-6 text-muted">/ {{ $attendanceSummary['half_day'] }}</span></div>
                <div class="pos-stat-sub text-muted">{{ $attendanceSummary['absent'] }} absent &bull; {{ $attendanceSummary['leave'] }} leave</div>
            </div>
        </div>
    </div>
    <div class="col-sm-12 col-xl-6">
        <div class="pos-glass-card pos-tone-warning">
            <div class="pos-stat-body">
                <div class="pos-stat-head">
                    <span class="pos-stat-label">Total Wages Payable / Earned</span>
                    <span class="pos-stat-icon"><i class="icon-base ti tabler-cash"></i></span>
                </div>
                <div class="pos-stat-value text-success">PKR {{ number_format($attendanceSummary['total_pay'], 0) }}</div>
                <div class="pos-stat-sub text-muted">Calculated dynamically using recorded multiplier at each attendance</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    {{-- Attendance Log --}}
    <div class="col-lg-7">
        <div class="pos-listing">
            <div class="pos-listing-toolbar d-flex justify-content-between align-items-center">
                <h5 class="pos-listing-title mb-0"><i class="icon-base ti tabler-calendar-check me-2 text-primary"></i> Recent Attendance History</h5>
                <a href="{{ route('attendance.history', ['worker_id' => $worker->id]) }}" class="btn btn-sm btn-outline-primary">Full History</a>
            </div>
            <div class="table-responsive">
                <table class="table align-middle table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Project</th>
                            <th>Status</th>
                            <th>Base Rate</th>
                            <th class="text-end">Earned</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($worker->attendance as $att)
                            <tr>
                                <td>{{ $att->attendance_date->format('d M Y') }}</td>
                                <td>{{ $att->project?->name }}</td>
                                <td>
                                    <span class="badge {{ $att->status->badgeClass() }}">
                                        {{ $att->status->label() }}
                                    </span>
                                </td>
                                <td>PKR {{ number_format($att->wage_at_time, 0) }}</td>
                                <td class="text-end fw-semibold text-success">PKR {{ number_format($att->payable_amount, 0) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">No attendance recorded yet for this worker.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Wage History Timeline --}}
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0"><i class="icon-base ti tabler-history me-2 text-info"></i> Wage Revision History</h5>
                <a href="{{ route('workers.edit', $worker) }}" class="btn btn-sm btn-outline-secondary">Revise Rate</a>
            </div>
            <div class="card-body p-3">
                <div class="list-group list-group-flush">
                    @forelse($worker->wageHistory as $history)
                        <div class="list-group-item px-0 py-3 border-bottom">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-bold fs-6 text-primary">PKR {{ number_format($history->daily_wage, 0) }}/day</span>
                                <span class="badge bg-label-secondary">Effective: {{ $history->effective_from->format('d M Y') }}</span>
                            </div>
                            <div class="small text-muted">{{ $history->reason ?? 'Rate revision' }}</div>
                            <div class="small text-muted" style="font-size: 0.75rem;">Recorded by {{ $history->changedBy?->name ?? 'System' }}</div>
                        </div>
                    @empty
                        <div class="text-center py-3 text-muted">Initial onboarding wage only.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
