@extends('layouts.app')

@section('title', 'Workforce Wages Report — ' . config('app.name'))

@section('content')
@section('content')
<div class="awt-glass-card awt-tone-primary mb-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="avatar avatar-lg rounded-3 bg-label-primary d-flex align-items-center justify-content-center">
                <i class="icon-base ti tabler-users fs-2"></i>
            </div>
            <div>
                <h4 class="awt-dash-title mb-1">Workforce Wages & Attendance Report</h4>
                <p class="awt-dash-subtitle mb-0">Cumulative summary of worker attendance shifts and payable wages.</p>
            </div>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary" onclick="window.print();">
                <i class="icon-base ti tabler-printer me-1"></i> Print Report
            </button>
            <a href="{{ route('reports.index') }}" class="btn btn-outline-primary">
                <i class="icon-base ti tabler-arrow-left me-1"></i> Reports Hub
            </a>
        </div>
    </div>
</div>

<div class="awt-listing-filter-strip awt-tone-secondary mb-4 d-print-none">
    <form method="GET" action="{{ route('reports.wages') }}" class="row g-3 align-items-end">
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
            <label class="form-label small fw-semibold text-muted text-uppercase">Contractor</label>
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
            <label class="form-label small fw-semibold text-muted text-uppercase">From Date</label>
            <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-semibold text-muted text-uppercase">To Date</label>
            <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
        </div>
        <div class="col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-primary w-100">
                <i class="icon-base ti tabler-filter me-1"></i> Apply
            </button>
            <a href="{{ route('reports.wages') }}" class="btn btn-outline-secondary">Reset</a>
        </div>
    </form>
</div>

{{-- Summary Top Banner --}}
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-md-4">
        <div class="awt-kpi-card p-3">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="awt-kpi-title">Total Wages Payable</span>
                <div class="avatar avatar-sm bg-label-warning rounded-circle d-flex align-items-center justify-content-center">
                    <i class="icon-base ti tabler-cash"></i>
                </div>
            </div>
            <div class="awt-kpi-value text-warning mb-1">PKR {{ number_format($totalWages, 0) }}</div>
            <div class="awt-kpi-footer text-muted">{{ $workerSummary->count() }} Workers Active</div>
        </div>
    </div>
</div>

<div class="awt-table-card awt-tone-secondary">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Worker</th>
                    <th>Trade</th>
                    <th>Contractor</th>
                    <th class="text-center">Full Days</th>
                    <th class="text-center">Half Days</th>
                    <th class="text-center">Absent</th>
                    <th class="text-center">Total Shifts</th>
                    <th class="text-end">Total Wage Earned</th>
                </tr>
            </thead>
            <tbody>
                @forelse($workerSummary as $row)
                    <tr>
                        <td>
                            <a href="{{ route('workers.show', $row['worker']) }}" class="fw-semibold text-primary text-decoration-none">
                                {{ $row['worker']->name }}
                            </a>
                            <div class="small text-muted">{{ $row['worker']->phone ?? '—' }}</div>
                        </td>
                        <td><span class="badge bg-label-secondary">{{ $row['worker']->worker_type }}</span></td>
                        <td>{{ $row['worker']->contractor?->name ?? '—' }}</td>
                        <td class="text-center"><span class="badge bg-label-success">{{ $row['full_days'] }}</span></td>
                        <td class="text-center"><span class="badge bg-label-warning">{{ $row['half_days'] }}</span></td>
                        <td class="text-center"><span class="badge bg-label-danger">{{ $row['absent'] }}</span></td>
                        <td class="text-center fw-bold">{{ $row['total_days'] }}</td>
                        <td class="text-end fw-bold text-success fs-6">
                            PKR {{ number_format($row['total_pay'], 2) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="p-0">
                            <div class="awt-empty-state">
                                <div class="awt-empty-state-icon text-muted mb-2">
                                    <i class="icon-base ti tabler-users-off fs-1"></i>
                                </div>
                                <h6 class="fw-semibold mb-1">No attendance or wage data found</h6>
                                <p class="text-muted small mb-0">Try widening your date range or adjusting filters.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
            @if($workerSummary->isNotEmpty())
                <tfoot class="border-top">
                    <tr>
                        <th colspan="7" class="text-end fw-bold">Total Payable Wages:</th>
                        <th class="text-end fw-bold fs-6 text-success">PKR {{ number_format($totalWages, 2) }}</th>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
</div>
@endsection
