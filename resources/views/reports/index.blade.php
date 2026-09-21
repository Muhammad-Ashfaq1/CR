@extends('layouts.app')

@section('title', 'Financial Reports — ' . config('app.name'))

@section('content')
<div class="awt-glass-card awt-tone-primary mb-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="avatar avatar-lg rounded-3 bg-label-primary d-flex align-items-center justify-content-center">
                <i class="icon-base ti tabler-chart-bar fs-2"></i>
            </div>
            <div>
                <h4 class="awt-dash-title mb-1">Reports & Financial Analytics</h4>
                <p class="awt-dash-subtitle mb-0">Detailed breakdown of material costs, labor wages, contractor balances, and executive site summaries.</p>
            </div>
        </div>
    </div>
</div>

{{-- Top Metrics Glass Cards --}}
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="awt-kpi-card p-3">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="awt-kpi-title">Contractor Disbursements</span>
                <div class="avatar avatar-sm bg-label-success rounded-circle d-flex align-items-center justify-content-center">
                    <i class="icon-base ti tabler-cash"></i>
                </div>
            </div>
            <div class="awt-kpi-value text-success mb-1">PKR {{ number_format($totalContractorPaid, 0) }}</div>
            <div class="awt-kpi-footer text-muted">Total cleared vouchers</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="awt-kpi-card p-3">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="awt-kpi-title">Direct Site Expenses</span>
                <div class="avatar avatar-sm bg-label-primary rounded-circle d-flex align-items-center justify-content-center">
                    <i class="icon-base ti tabler-receipt"></i>
                </div>
            </div>
            <div class="awt-kpi-value text-primary mb-1">PKR {{ number_format($totalExpenses, 0) }}</div>
            <div class="awt-kpi-footer text-muted">Materials, equipment & fuel</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="awt-kpi-card p-3">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="awt-kpi-title">Labor Wages Computed</span>
                <div class="avatar avatar-sm bg-label-warning rounded-circle d-flex align-items-center justify-content-center">
                    <i class="icon-base ti tabler-hammer"></i>
                </div>
            </div>
            <div class="awt-kpi-value text-warning mb-1">PKR {{ number_format($totalWagesRecorded, 0) }}</div>
            <div class="awt-kpi-footer text-muted">From recorded attendance</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="awt-kpi-card p-3">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="awt-kpi-title">Combined Outlay</span>
                <div class="avatar avatar-sm bg-label-info rounded-circle d-flex align-items-center justify-content-center">
                    <i class="icon-base ti tabler-chart-pie"></i>
                </div>
            </div>
            <div class="awt-kpi-value text-info mb-1">PKR {{ number_format($totalInvestment, 0) }}</div>
            <div class="awt-kpi-footer text-muted">Total platform spend</div>
        </div>
    </div>
</div>

{{-- Report Modules Grid --}}
<div class="row g-4">
    <div class="col-md-6 col-lg-3">
        <div class="awt-glass-card h-100 p-3 d-flex flex-column justify-content-between">
            <div>
                <div class="rounded-3 d-flex align-items-center justify-content-center mb-3 bg-label-primary" style="width: 48px; height: 48px;">
                    <i class="icon-base ti tabler-chart-donut fs-4 text-primary"></i>
                </div>
                <h5 class="fw-bold text-body mb-1">Expense Breakdown</h5>
                <p class="text-muted small">Analyze material, equipment, and utility expenses categorized by project and vendor.</p>
            </div>
            <a href="{{ route('reports.expenses') }}" class="btn btn-outline-primary btn-sm mt-3">
                Open Report <i class="icon-base ti tabler-arrow-right ms-1"></i>
            </a>
        </div>
    </div>

    <div class="col-md-6 col-lg-3">
        <div class="awt-glass-card h-100 p-3 d-flex flex-column justify-content-between">
            <div>
                <div class="rounded-3 d-flex align-items-center justify-content-center mb-3 bg-label-warning" style="width: 48px; height: 48px;">
                    <i class="icon-base ti tabler-users fs-4 text-warning"></i>
                </div>
                <h5 class="fw-bold text-body mb-1">Workforce & Wages</h5>
                <p class="text-muted small">Audit daily attendance, full/half day shifts, and cumulative wage liabilities per worker.</p>
            </div>
            <a href="{{ route('reports.wages') }}" class="btn btn-outline-warning btn-sm mt-3">
                Open Report <i class="icon-base ti tabler-arrow-right ms-1"></i>
            </a>
        </div>
    </div>

    <div class="col-md-6 col-lg-3">
        <div class="awt-glass-card h-100 p-3 d-flex flex-column justify-content-between">
            <div>
                <div class="rounded-3 d-flex align-items-center justify-content-center mb-3 bg-label-success" style="width: 48px; height: 48px;">
                    <i class="icon-base ti tabler-file-analytics fs-4 text-success"></i>
                </div>
                <h5 class="fw-bold text-body mb-1">Contractor Ledgers</h5>
                <p class="text-muted small">Detailed statement of account, contract commitments, payment vouchers, and running balance.</p>
            </div>
            <a href="{{ route('reports.contractor-ledger') }}" class="btn btn-outline-success btn-sm mt-3">
                Open Report <i class="icon-base ti tabler-arrow-right ms-1"></i>
            </a>
        </div>
    </div>

    <div class="col-md-6 col-lg-3">
        <div class="awt-glass-card h-100 p-3 d-flex flex-column justify-content-between">
            <div>
                <div class="rounded-3 d-flex align-items-center justify-content-center mb-3 bg-label-info" style="width: 48px; height: 48px;">
                    <i class="icon-base ti tabler-building-skyscraper fs-4 text-info"></i>
                </div>
                <h5 class="fw-bold text-body mb-1">Project Cost Rollup</h5>
                <p class="text-muted small">Comprehensive executive project cost rollups comparing contracts, direct materials, and labor.</p>
            </div>
            <a href="{{ route('reports.project-summary') }}" class="btn btn-outline-info btn-sm mt-3">
                Open Report <i class="icon-base ti tabler-arrow-right ms-1"></i>
            </a>
        </div>
    </div>
</div>
@endsection
