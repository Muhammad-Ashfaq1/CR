@extends('layouts.app')

@section('title', 'Project Financial Summary — ' . config('app.name'))

@section('content')
<div class="cst-page-header">
    <div>
        <h1 class="cst-page-title">Executive Project Financial Summary</h1>
        <p class="cst-page-subtitle">Holistic cost aggregation across contractor contracts, materials, equipment, and labor.</p>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-outline-secondary" onclick="window.print();">
            <i class="ti ti-printer me-1"></i> Print Summary
        </button>
        <a href="{{ route('reports.index') }}" class="btn btn-outline-primary">
            &larr; Reports Hub
        </a>
    </div>
</div>

<div class="cst-card mb-4 d-print-none">
    <div class="cst-card-body p-3">
        <form method="GET" action="{{ route('reports.project-summary') }}" class="row g-3 align-items-end">
            <div class="col-md-8">
                <label class="form-label small fw-semibold">Select Construction Project</label>
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
</div>

@if(!$project || !$stats)
    <div class="cst-card text-center py-5 text-muted">
        <i class="ti ti-building-off fs-1 d-block mb-2 opacity-50"></i>
        <h5>No project selected.</h5>
    </div>
@else
    {{-- Project Header Info --}}
    <div class="cst-card p-4 mb-4 border">
        <div class="d-flex justify-content-between align-items-start border-bottom pb-3 mb-3">
            <div>
                <h3 class="fw-bold mb-1 text-dark">{{ $project->name }}</h3>
                <div class="text-muted">
                    <i class="ti ti-map-pin me-1"></i> {{ $project->location ?? 'No location' }} &bull;
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
                <div class="p-3 bg-light rounded text-center">
                    <div class="small text-muted">Total Committed Project Budget</div>
                    <div class="fs-4 fw-bold text-dark">PKR {{ number_format($stats['totalCommitment'], 0) }}</div>
                    <div class="small text-muted">Contracts + Direct Expenses</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 bg-light rounded text-center">
                    <div class="small text-muted">Total Cash Outflow to Date</div>
                    <div class="fs-4 fw-bold text-success">PKR {{ number_format($stats['grandTotalSpent'], 0) }}</div>
                    <div class="small text-muted">Contractor Paid + Direct Expenses</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 bg-light rounded text-center">
                    <div class="small text-muted">Pending Contractor Balance</div>
                    <div class="fs-4 fw-bold text-danger">PKR {{ number_format($stats['contractorRemaining'], 0) }}</div>
                    <div class="small text-muted">Future disbursements</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Breakdown Grid --}}
    <div class="row g-4">
        {{-- Left: Financial Statement --}}
        <div class="col-lg-7">
            <div class="cst-card">
                <div class="cst-card-header">
                    <h5 class="cst-card-title mb-0">Cost Structure Breakdown</h5>
                </div>
                <div class="cst-card-body p-0">
                    <table class="table table-bordered align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Expense Classification</th>
                                <th class="text-end">Amount (PKR)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <div class="fw-semibold">Contractor Payments Cleared</div>
                                    <div class="small text-muted">Milestone & advance disbursements</div>
                                </td>
                                <td class="text-end fw-bold text-success">PKR {{ number_format($stats['contractorPaid'], 2) }}</td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="fw-semibold">Building & Construction Materials</div>
                                    <div class="small text-muted">Cement, Bricks, Steel, Sand, Crush, Gravel, etc.</div>
                                </td>
                                <td class="text-end fw-bold">PKR {{ number_format($stats['materials'], 2) }}</td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="fw-semibold">Labor & Direct Site Wages</div>
                                    <div class="small text-muted">Direct wages and petty daily labor expenses</div>
                                </td>
                                <td class="text-end fw-bold">PKR {{ number_format($stats['labor'], 2) }}</td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="fw-semibold">Machinery & Equipment Rental</div>
                                    <div class="small text-muted">Mixer machines, excavators, scaffolding, etc.</div>
                                </td>
                                <td class="text-end fw-bold">PKR {{ number_format($stats['equipment'], 2) }}</td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="fw-semibold">Utilities, Fuel & Admin Overhead</div>
                                    <div class="small text-muted">Water tankers, electricity, generator fuel, tea, food</div>
                                </td>
                                <td class="text-end fw-bold">PKR {{ number_format($stats['utilities'] + $stats['misc'], 2) }}</td>
                            </tr>
                        </tbody>
                        <tfoot class="table-light">
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
            <div class="cst-card mb-4">
                <div class="cst-card-header">
                    <h5 class="cst-card-title mb-0">Workforce & Operations</h5>
                </div>
                <div class="cst-card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted">Registered Site Workers</span>
                            <span class="fw-bold">{{ $stats['workerCount'] }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted">Attendance Days Logged</span>
                            <span class="fw-bold">{{ $stats['attendanceDaysCount'] }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted">Computed Workforce Wages</span>
                            <span class="fw-bold text-success">PKR {{ number_format($stats['totalWagesRecorded'], 0) }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted">Contractors Engaged</span>
                            <span class="fw-bold">{{ $project->contractors->count() }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endif
@endsection
