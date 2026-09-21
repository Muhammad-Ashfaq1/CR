@extends('layouts.app')

@section('title', 'Expense Breakdown Report — ' . config('app.name'))

@section('content')
<div class="awt-glass-card awt-tone-primary mb-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="avatar avatar-lg rounded-3 bg-label-primary d-flex align-items-center justify-content-center">
                <i class="icon-base ti tabler-chart-donut fs-2"></i>
            </div>
            <div>
                <h4 class="awt-dash-title mb-1">Expense Breakdown Report</h4>
                <p class="awt-dash-subtitle mb-0">Granular report of all materials and site expenses by category and project.</p>
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
    <form method="GET" action="{{ route('reports.expenses') }}" class="row g-3 align-items-end">
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
            <label class="form-label small fw-semibold text-muted text-uppercase">Category</label>
            <select name="category_id" class="form-select">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                        {{ $cat->name }} ({{ $cat->type }})
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
            <a href="{{ route('reports.expenses') }}" class="btn btn-outline-secondary">Reset</a>
        </div>
    </form>
</div>

{{-- Total & Breakdown Cards --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="awt-kpi-card p-4 h-100 d-flex flex-column justify-content-center">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="awt-kpi-title">Total Expense Amount</span>
                <div class="avatar avatar-sm bg-label-primary rounded-circle d-flex align-items-center justify-content-center">
                    <i class="icon-base ti tabler-receipt"></i>
                </div>
            </div>
            <div class="awt-kpi-value text-primary mb-1">PKR {{ number_format($totalAmount, 0) }}</div>
            <div class="awt-kpi-footer text-muted">{{ $expenses->count() }} line items in filtered view</div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="awt-glass-card p-3 h-100">
            <h6 class="fw-bold mb-3"><i class="icon-base ti tabler-chart-pie me-1 text-primary"></i> Category Distribution</h6>
            <div class="row g-2">
                @forelse($categoryBreakdown as $b)
                    @php $p = $totalAmount > 0 ? round(($b['total'] / $totalAmount) * 100, 1) : 0; @endphp
                    <div class="col-sm-6 col-md-4">
                        <div class="p-2 border rounded bg-body-tertiary">
                            <div class="d-flex justify-content-between small">
                                <span class="fw-semibold text-truncate">{{ $b['category'] }}</span>
                                <span class="text-muted">{{ $p }}%</span>
                            </div>
                            <div class="fw-bold fs-6 mt-1 text-primary">PKR {{ number_format($b['total'], 0) }}</div>
                            <div class="progress mt-1" style="height: 4px;">
                                <div class="progress-bar" style="width: {{ $p }}%; background-color: {{ $b['color'] }};"></div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-muted small">No category data available.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<div class="awt-table-card awt-tone-secondary">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Project</th>
                    <th>Category</th>
                    <th>Vendor / Description</th>
                    <th>Payment Mode</th>
                    <th class="text-end">Amount (PKR)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($expenses as $exp)
                    <tr>
                        <td>{{ $exp->expense_date->format('d M Y') }}</td>
                        <td>{{ $exp->project?->name }}</td>
                        <td>
                            <span class="badge" style="background-color: {{ $exp->category?->color }}20; color: {{ $exp->category?->color }};">
                                {{ $exp->category?->name ?? 'Uncategorized' }}
                            </span>
                        </td>
                        <td>
                            <div class="fw-semibold text-body">{{ $exp->vendor ?? '—' }}</div>
                            <div class="small text-muted">{{ $exp->description }}</div>
                        </td>
                        <td>
                            <span class="badge bg-label-secondary">{{ $exp->payment_method ?? 'Cash' }}</span>
                        </td>
                        <td class="text-end fw-bold text-body">
                            {{ number_format($exp->amount, 2) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-0">
                            <div class="awt-empty-state">
                                <div class="awt-empty-state-icon text-muted mb-2">
                                    <i class="icon-base ti tabler-receipt-off fs-1"></i>
                                </div>
                                <h6 class="fw-semibold mb-1">No expense records found</h6>
                                <p class="text-muted small mb-0">Try changing your filter settings above.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
            @if($expenses->isNotEmpty())
                <tfoot class="border-top">
                    <tr>
                        <th colspan="5" class="text-end fw-bold">Grand Total:</th>
                        <th class="text-end fw-bold fs-6 text-primary">PKR {{ number_format($totalAmount, 2) }}</th>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
</div>
@endsection
