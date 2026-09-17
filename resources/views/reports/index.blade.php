@extends('layouts.app')

@section('title', 'Financial Reports — ' . config('app.name'))

@section('content')
<div class="cst-page-header">
    <div>
        <h1 class="cst-page-title">Reports & Financial Analytics</h1>
        <p class="cst-page-subtitle">Detailed breakdown of material costs, labor wages, contractor balances, and project summaries.</p>
    </div>
</div>

{{-- Top Metrics --}}
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="cst-card cst-stat-card">
            <div class="cst-stat-label">Total Outlay to Contractors</div>
            <div class="cst-stat-value text-success">PKR {{ number_format($totalContractorPaid, 0) }}</div>
            <div class="cst-stat-sub text-muted">Cleared payments</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="cst-card cst-stat-card">
            <div class="cst-stat-label">Total Direct Expenses</div>
            <div class="cst-stat-value text-primary">PKR {{ number_format($totalExpenses, 0) }}</div>
            <div class="cst-stat-sub text-muted">Materials & Site bills</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="cst-card cst-stat-card">
            <div class="cst-stat-label">Total Labor Wages Computed</div>
            <div class="cst-stat-value text-warning">PKR {{ number_format($totalWagesRecorded, 0) }}</div>
            <div class="cst-stat-sub text-muted">Recorded attendance</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="cst-card cst-stat-card border-primary">
            <div class="cst-stat-label text-primary fw-bold">Combined Outlay</div>
            <div class="cst-stat-value fs-4 text-primary fw-bold">PKR {{ number_format($totalInvestment, 0) }}</div>
            <div class="cst-stat-sub text-muted">Total platform spend</div>
        </div>
    </div>
</div>

{{-- Report Modules Grid --}}
<div class="row g-4">
    <div class="col-md-6 col-lg-3">
        <div class="cst-card h-100 p-4 d-flex flex-column justify-content-between">
            <div>
                <div class="rounded-circle d-flex align-items-center justify-content-center mb-3 bg-label-primary" style="width: 48px; height: 48px;">
                    <i class="ti ti-chart-donut fs-4 text-primary"></i>
                </div>
                <h5 class="fw-bold text-dark mb-1">Expense Breakdown</h5>
                <p class="text-muted small">Analyze material, equipment, and utility expenses categorized by project and vendor.</p>
            </div>
            <a href="{{ route('reports.expenses') }}" class="btn btn-outline-primary btn-sm mt-3">
                Open Report &rarr;
            </a>
        </div>
    </div>

    <div class="col-md-6 col-lg-3">
        <div class="cst-card h-100 p-4 d-flex flex-column justify-content-between">
            <div>
                <div class="rounded-circle d-flex align-items-center justify-content-center mb-3 bg-label-warning" style="width: 48px; height: 48px;">
                    <i class="ti ti-users fs-4 text-warning"></i>
                </div>
                <h5 class="fw-bold text-dark mb-1">Workforce & Wages</h5>
                <p class="text-muted small">Audit daily attendance, full/half day shifts, and cumulative wage liabilities per worker.</p>
            </div>
            <a href="{{ route('reports.wages') }}" class="btn btn-outline-warning text-dark btn-sm mt-3">
                Open Report &rarr;
            </a>
        </div>
    </div>

    <div class="col-md-6 col-lg-3">
        <div class="cst-card h-100 p-4 d-flex flex-column justify-content-between">
            <div>
                <div class="rounded-circle d-flex align-items-center justify-content-center mb-3 bg-label-success" style="width: 48px; height: 48px;">
                    <i class="ti ti-file-analytics fs-4 text-success"></i>
                </div>
                <h5 class="fw-bold text-dark mb-1">Contractor Ledgers</h5>
                <p class="text-muted small">Detailed statement of account, contract commitments, payment vouchers, and running balance.</p>
            </div>
            <a href="{{ route('reports.contractor-ledger') }}" class="btn btn-outline-success btn-sm mt-3">
                Open Report &rarr;
            </a>
        </div>
    </div>

    <div class="col-md-6 col-lg-3">
        <div class="cst-card h-100 p-4 d-flex flex-column justify-content-between">
            <div>
                <div class="rounded-circle d-flex align-items-center justify-content-center mb-3 bg-label-info" style="width: 48px; height: 48px;">
                    <i class="ti ti-building-skyscraper fs-4 text-info"></i>
                </div>
                <h5 class="fw-bold text-dark mb-1">Project Summary</h5>
                <p class="text-muted small">Comprehensive executive project cost rollups comparing contracts, materials, and labor.</p>
            </div>
            <a href="{{ route('reports.project-summary') }}" class="btn btn-outline-info text-dark btn-sm mt-3">
                Open Report &rarr;
            </a>
        </div>
    </div>
</div>
@endsection
