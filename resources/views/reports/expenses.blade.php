@extends('layouts.app')

@section('title', 'Expense Breakdown Report — ' . config('app.name'))

@section('content')
<div class="pos-glass-intro pos-tone-primary mb-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <h4 class="pos-glass-intro-title mb-1">
                <i class="icon-base ti tabler-chart-donut me-2 text-primary"></i> Expense Breakdown Report
            </h4>
            <div class="pos-glass-intro-sub">Granular report of all materials and site expenses by category and project.</div>
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

<div class="pos-listing-panel mb-4 d-print-none">
    <form method="GET" action="{{ route('reports.expenses') }}" class="row g-3 align-items-end">
        <div class="col-md-3">
            <label class="form-label small fw-semibold">Project</label>
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
            <label class="form-label small fw-semibold">Category</label>
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
            <label class="form-label small fw-semibold">From Date</label>
            <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-semibold">To Date</label>
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
        <div class="pos-glass-card pos-tone-primary h-100">
            <div class="pos-stat-body">
                <div class="pos-stat-head">
                    <span class="pos-stat-label">Total Expense Amount</span>
                    <span class="pos-stat-icon"><i class="icon-base ti tabler-receipt"></i></span>
                </div>
                <div class="pos-stat-value text-primary">PKR {{ number_format($totalAmount, 0) }}</div>
                <div class="pos-stat-sub text-muted">{{ $expenses->count() }} line items in filtered view</div>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card border-0 shadow-sm p-3 h-100">
            <h6 class="fw-bold mb-3"><i class="icon-base ti tabler-chart-pie me-1 text-primary"></i> Category Distribution</h6>
            <div class="row g-2">
                @forelse($categoryBreakdown as $b)
                    @php $p = $totalAmount > 0 ? round(($b['total'] / $totalAmount) * 100, 1) : 0; @endphp
                    <div class="col-sm-6 col-md-4">
                        <div class="p-2 border rounded bg-light">
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

<div class="pos-listing">
    <div class="table-responsive">
        <table class="table align-middle table-striped mb-0">
            <thead class="table-light">
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
                            <div class="fw-semibold text-dark">{{ $exp->vendor ?? '—' }}</div>
                            <div class="small text-muted">{{ $exp->description }}</div>
                        </td>
                        <td>
                            <span class="badge bg-label-secondary">{{ $exp->payment_method ?? 'Cash' }}</span>
                        </td>
                        <td class="text-end fw-bold text-dark">
                            {{ number_format($exp->amount, 2) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">No expense records found.</td>
                    </tr>
                @endforelse
            </tbody>
            @if($expenses->isNotEmpty())
                <tfoot class="table-light">
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
