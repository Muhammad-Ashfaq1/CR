@extends('layouts.app')

@section('title', 'Daily Attendance Sheet — ' . config('app.name'))

@section('content')
{{-- Banner Intro --}}
<div class="pos-glass-card pos-tone-warning mb-4">
    <div class="pos-glass-intro">
        <div class="pos-glass-intro-icon">
            <i class="icon-base ti tabler-calendar-check" aria-hidden="true"></i>
        </div>
        <div class="pos-glass-intro-content">
            <div class="pos-glass-intro-title">
                <h4 class="mb-1 text-heading fw-bold">Daily Attendance Sheet</h4>
                <span class="badge bg-label-warning">Workforce Dispatch</span>
            </div>
            <p class="pos-glass-intro-subtitle mb-0">Record daily attendance for site workers. Wages, shift multipliers, and wage history are computed automatically.</p>
        </div>
        <div class="pos-glass-intro-actions">
            <a href="{{ route('attendance.history') }}" class="btn btn-label-secondary">
                <i class="icon-base ti tabler-history me-1"></i> Attendance History
            </a>
        </div>
    </div>
</div>

{{-- Filter Toolbar --}}
<div class="pos-glass-card pos-tone-secondary mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('attendance.index') }}" class="row g-3 align-items-end" id="attendanceFilterForm">
            <div class="col-md-5">
                <label class="form-label small fw-semibold">Select Construction Site</label>
                <select name="project_id" class="form-select" onchange="document.getElementById('attendanceFilterForm').submit();">
                    @foreach($projects as $p)
                        <option value="{{ $p->id }}" {{ $selectedProjectId == $p->id ? 'selected' : '' }}>
                            {{ $p->name }} {{ $p->site_name ? "({$p->site_name})" : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Attendance Date</label>
                <div class="input-group">
                    <input type="date" name="date" class="form-control" value="{{ $date }}" onchange="document.getElementById('attendanceFilterForm').submit();">
                    <span class="input-group-text bg-light text-muted">{{ $carbonDate->format('l') }}</span>
                </div>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100">Load Attendance Sheet</button>
            </div>
        </form>
    </div>
</div>

@if(!$selectedProjectId || $workers->isEmpty())
    <div class="pos-glass-card pos-tone-secondary text-center py-5 text-muted">
        <i class="icon-base ti tabler-calendar-off fs-1 d-block mb-2 opacity-50"></i>
        <h5>No active workers found for this project.</h5>
        <p class="mb-3">Make sure workers are registered and assigned to this project site.</p>
        <a href="{{ route('workers.create') }}" class="btn btn-primary btn-sm">Register Worker</a>
    </div>
@else
    <form action="{{ route('attendance.store-daily') }}" method="POST">
        @csrf
        <input type="hidden" name="project_id" value="{{ $selectedProjectId }}">
        <input type="hidden" name="attendance_date" value="{{ $date }}">

        <div class="pos-listing">
            <div class="pos-glass-card pos-tone-secondary pos-listing-panel">
                <div class="pos-listing-toolbar d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="pos-listing-title mb-0">Site Roster ({{ $workers->count() }} Workers)</h5>
                        <small class="text-muted">Date: <strong>{{ $carbonDate->format('d M Y (l)') }}</strong></small>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-label-success" onclick="markAll('full_day')">
                            <i class="icon-base ti tabler-checks me-1"></i> All Full Day
                        </button>
                        <button type="button" class="btn btn-sm btn-label-danger" onclick="markAll('absent')">
                            <i class="icon-base ti tabler-x me-1"></i> All Absent
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Worker Details</th>
                                <th>Trade Skill</th>
                                <th>Contractor</th>
                                <th>Effective Wage</th>
                                <th style="min-width: 340px;">Attendance Status</th>
                                <th>Notes / Shift Memo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($workers as $worker)
                                @php
                                    $existing = $existingAttendance->get($worker->id);
                                    $currentStatus = $existing ? $existing->status->value : 'full_day';
                                    $effectiveWage = $worker->wageForDate($carbonDate);
                                @endphp
                                <tr>
                                    <td>
                                        <div class="fw-semibold text-heading">{{ $worker->name }}</div>
                                        <div class="small text-muted">{{ $worker->phone ?? 'No phone' }}</div>
                                    </td>
                                    <td><span class="badge bg-label-info">{{ $worker->worker_type }}</span></td>
                                    <td><div class="small text-muted">{{ $worker->contractor?->name }}</div></td>
                                    <td class="fw-semibold text-dark">
                                        PKR {{ number_format($effectiveWage, 0) }}
                                    </td>
                                    <td>
                                        <div class="btn-group w-100" role="group">
                                            <input type="radio" class="btn-check" name="attendance[{{ $worker->id }}][status]" id="status_{{ $worker->id }}_full" value="full_day" {{ $currentStatus === 'full_day' ? 'checked' : '' }}>
                                            <label class="btn btn-outline-success btn-sm" for="status_{{ $worker->id }}_full">
                                                <i class="icon-base ti tabler-check"></i> Full
                                            </label>

                                            <input type="radio" class="btn-check" name="attendance[{{ $worker->id }}][status]" id="status_{{ $worker->id }}_half" value="half_day" {{ $currentStatus === 'half_day' ? 'checked' : '' }}>
                                            <label class="btn btn-outline-warning btn-sm" for="status_{{ $worker->id }}_half">
                                                <i class="icon-base ti tabler-circle-half"></i> Half
                                            </label>

                                            <input type="radio" class="btn-check" name="attendance[{{ $worker->id }}][status]" id="status_{{ $worker->id }}_absent" value="absent" {{ $currentStatus === 'absent' ? 'checked' : '' }}>
                                            <label class="btn btn-outline-danger btn-sm" for="status_{{ $worker->id }}_absent">
                                                <i class="icon-base ti tabler-x"></i> Absent
                                            </label>

                                            <input type="radio" class="btn-check" name="attendance[{{ $worker->id }}][status]" id="status_{{ $worker->id }}_leave" value="leave" {{ $currentStatus === 'leave' ? 'checked' : '' }}>
                                            <label class="btn btn-outline-secondary btn-sm" for="status_{{ $worker->id }}_leave">
                                                Leave
                                            </label>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="text" name="attendance[{{ $worker->id }}][notes]" class="form-control form-control-sm" placeholder="Optional notes..." value="{{ $existing?->notes }}">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="card-footer p-3 bg-light d-flex justify-content-between align-items-center">
                    <span class="text-muted small">Wage Calculation: Full Day (100%), Half Day (50%), Absent/Leave (0%)</span>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="icon-base ti tabler-device-floppy me-1"></i> Save Daily Attendance
                    </button>
                </div>
            </div>
        </div>
    </form>
@endif

@push('scripts')
<script>
function markAll(status) {
    document.querySelectorAll(`input[type="radio"][value="${status}"]`).forEach(el => {
        el.checked = true;
    });
}
</script>
@endpush
@endsection
