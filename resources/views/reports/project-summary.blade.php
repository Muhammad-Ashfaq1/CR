@extends('layouts.app')

@section('title', 'Project Financial Summary — ' . config('app.name'))

@section('content')
<div class="awt-glass-card awt-tone-primary mb-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="avatar avatar-lg rounded-3 bg-label-primary d-flex align-items-center justify-content-center">
                <i class="icon-base ti tabler-building-skyscraper fs-2"></i>
            </div>
            <div>
                <h4 class="awt-dash-title mb-1">Executive Project Financial Summary</h4>
                <p class="awt-dash-subtitle mb-0">Holistic cost aggregation across contractor contracts, materials, equipment, and labor.</p>
            </div>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary" onclick="window.print();">
                <i class="icon-base ti tabler-printer me-1"></i> Print Summary
            </button>
            <a href="{{ route('reports.index') }}" class="btn btn-outline-primary">
                <i class="icon-base ti tabler-arrow-left me-1"></i> Reports Hub
            </a>
        </div>
    </div>
</div>

<div class="awt-listing-filter-strip awt-tone-secondary mb-4 d-print-none">
    <form method="GET" action="{{ route('reports.project-summary') }}" class="row g-3 align-items-end">
        <div class="col-md-8">
            <label class="form-label small fw-semibold text-muted text-uppercase">Select Construction Project</label>
            <select name="project_id" class="form-select" onchange="this.form.submit()">
                @foreach($projects as $p)
                    <option value="{{ $p->id }}" {{ $selectedProjectId == $p->id ? 'selected' : '' }}>
                        {{ $p->name }} {{ $p->site_name ? "({$p->site_name})" : '' }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <button type="submit" class="btn btn-primary w-100">Load Project Summary</button>
        </div>
    </form>
</div>

@if(!$project || !$stats)
    <div class="awt-glass-card p-5 text-center">
        <div class="awt-empty-state-icon text-muted mb-2">
            <i class="icon-base ti tabler-building-off fs-1"></i>
        </div>
        <h5 class="fw-semibold mb-1">No project selected</h5>
        <p class="text-muted small mb-0">Choose a project from the dropdown above to load its financial summary.</p>
    </div>
@else
    {{-- Project Header Info --}}
    <div class="awt-glass-card p-4 mb-4">
        <div class="d-flex justify-content-between align-items-start border-bottom pb-3 mb-3">
            <div>
                <h3 class="fw-bold mb-1 text-body">{{ $project->name }}</h3>
                <div class="text-muted small">
                    <i class="icon-base ti tabler-map-pin me-1"></i> {{ $project->location ?? 'No location' }} &bull;
                    Site: {{ $project->site_name ?? '—' }} &bull;
                    Owner: {{ $project->owner?->name ?? '—' }}
                </div>
            </div>
            <div class="text-end">
                <span class="badge {{ $project->status->badgeClass() }} p-2">{{ $project->status->label() }}</span>
            </div>
        </div>

        {{-- Grand Totals --}}
        <div class="row g-3">
            <div class="col-md-4">
                <div class="awt-kpi-card p-3 text-center">
                    <div class="awt-kpi-title mb-1">Total Committed Project Budget</div>
                    <div class="awt-kpi-value text-body">PKR {{ number_format($stats['totalCommitment'], 0) }}</div>
                    <div class="awt-kpi-footer text-muted">Contracts + Direct Expenses</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="awt-kpi-card p-3 text-center">
                    <div class="awt-kpi-title mb-1">Total Cash Outflow to Date</div>
                    <div class="awt-kpi-value text-success">PKR {{ number_format($stats['grandTotalSpent'], 0) }}</div>
                    <div class="awt-kpi-footer text-muted">Contractor Paid + Direct Expenses</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="awt-kpi-card p-3 text-center">
                    <div class="awt-kpi-title mb-1">Pending Contractor Balance</div>
                    <div class="awt-kpi-value text-danger">PKR {{ number_format($stats['contractorRemaining'], 0) }}</div>
                    <div class="awt-kpi-footer text-muted">Future disbursements</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Breakdown Grid --}}
    <div class="row g-4">
        {{-- Left: Financial Statement --}}
        <div class="col-lg-7">
            <div class="awt-table-card awt-tone-secondary">
                <div class="p-3 border-bottom">
                    <h5 class="fw-semibold mb-0">Cost Structure Breakdown</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Expense Classification</th>
                                <th class="text-end">Amount (PKR)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <div class="fw-semibold text-body">Contractor Payments Cleared</div>
                                    <div class="small text-muted">Milestone &amp; advance disbursements</div>
                                </td>
                                <td class="text-end fw-bold text-success">PKR {{ number_format($stats['contractorPaid'], 2) }}</td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="fw-semibold text-body">Building &amp; Construction Materials</div>
                                    <div class="small text-muted">Cement, Bricks, Steel, Sand, Crush, Gravel, etc.</div>
                                </td>
                                <td class="text-end fw-bold text-body">PKR {{ number_format($stats['materials'], 2) }}</td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="fw-semibold text-body">Labor &amp; Direct Site Wages</div>
                                    <div class="small text-muted">Direct wages and petty daily labor expenses</div>
                                </td>
                                <td class="text-end fw-bold text-body">PKR {{ number_format($stats['labor'], 2) }}</td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="fw-semibold text-body">Machinery &amp; Equipment Rental</div>
                                    <div class="small text-muted">Mixer machines, excavators, scaffolding, etc.</div>
                                </td>
                                <td class="text-end fw-bold text-body">PKR {{ number_format($stats['equipment'], 2) }}</td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="fw-semibold text-body">Utilities, Fuel &amp; Admin Overhead</div>
                                    <div class="small text-muted">Water tankers, electricity, generator fuel, tea, food</div>
                                </td>
                                <td class="text-end fw-bold text-body">PKR {{ number_format($stats['utilities'] + $stats['misc'], 2) }}</td>
                            </tr>
                        </tbody>
                        <tfoot class="border-top">
                            <tr>
                                <th class="fw-bold fs-6">Grand Total Spent to Date:</th>
                                <th class="text-end fw-bold fs-5 text-primary">PKR {{ number_format($stats['grandTotalSpent'], 2) }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        {{-- Right: Site Metrics --}}
        <div class="col-lg-5">
            <div class="awt-glass-card mb-4 p-4">
                <h5 class="fw-semibold mb-3 border-bottom pb-2">Workforce & Operations</h5>
                <ul class="list-group list-group-flush bg-transparent">
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 bg-transparent">
                        <span class="text-muted">Registered Site Workers</span>
                        <span class="fw-bold text-body">{{ $stats['workerCount'] }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 bg-transparent">
                        <span class="text-muted">Attendance Days Logged</span>
                        <span class="fw-bold text-body">{{ $stats['attendanceDaysCount'] }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 bg-transparent">
                        <span class="text-muted">Computed Workforce Wages</span>
                        <span class="fw-bold text-success">PKR {{ number_format($stats['totalWagesRecorded'], 0) }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 bg-transparent">
                        <span class="text-muted">Contractors Engaged</span>
                        <span class="fw-bold text-body">{{ $project->contractors->count() }}</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
@endif
@endsection
