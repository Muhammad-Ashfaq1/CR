@extends('layouts.app')

@section('title', 'Workforce Wages Report — ' . config('app.name'))

@section('content')
<div class="cst-page-header">
    <div>
        <h1 class="cst-page-title">Workforce Wages & Attendance Report</h1>
        <p class="cst-page-subtitle">Cumulative summary of worker attendance shifts and payable wages.</p>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-outline-secondary" onclick="window.print();">
            <i class="ti ti-printer me-1"></i> Print Report
        </button>
        <a href="{{ route('reports.index') }}" class="btn btn-outline-primary">
            &larr; Reports Hub
        </a>
    </div>
</div>

<div class="cst-card mb-4 d-print-none">
    <div class="cst-card-body p-3">
        <form method="GET" action="{{ route('reports.wages') }}" class="row g-3 align-items-end">
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
                <label class="form-label small">From Date</label>
                <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small">To Date</label>
                <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100">Apply</button>
                <a href="{{ route('reports.wages') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

{{-- Summary Top Banner --}}
<div class="alert alert-warning d-flex align-items-center justify-content-between p-3 mb-4 rounded-3 border-0 bg-warning bg-opacity-10 text-dark">
    <div class="d-flex align-items-center">
        <i class="ti ti-users fs-2 text-warning me-3"></i>
        <div>
            <div class="small fw-semibold text-muted">Total Wages Payable to Workforce</div>
            <div class="fs-4 fw-bold text-dark">PKR {{ number_format($totalWages, 0) }}</div>
        </div>
    </div>
    <span class="badge bg-warning text-dark">{{ $workerSummary->count() }} Workers Active</span>
</div>

<div class="cst-card">
    <div class="cst-card-header">
        <h5 class="cst-card-title mb-0">Worker-Level Summary</h5>
    </div>
    <div class="cst-card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle table-striped mb-0">
                <thead class="table-light">
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
                                <a href="{{ route('workers.show', $row['worker']) }}" class="fw-semibold text-dark text-decoration-none">
                                    {{ $row['worker']->name }}
                                </a>
                                <div class="small text-muted">{{ $row['worker']->phone ?? '—' }}</div>
                            </td>
                            <td><span class="badge bg-light text-dark border">{{ $row['worker']->worker_type }}</span></td>
                            <td>{{ $row['worker']->contractor?->name }}</td>
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
                            <td colspan="8" class="text-center py-4 text-muted">No attendance or wage data found for selected period.</td>
                        </tr>
                    @endforelse
                </tbody>
                @if($workerSummary->isNotEmpty())
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="7" class="text-end fw-bold">Total Payable Wages:</th>
                            <th class="text-end fw-bold fs-6 text-success">PKR {{ number_format($totalWages, 2) }}</th>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
@endsection
