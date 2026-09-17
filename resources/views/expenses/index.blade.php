@extends('layouts.app')

@section('title', 'Expenses — ' . config('app.name'))

@section('content')
{{-- Banner Intro --}}
<div class="pos-glass-card pos-tone-warning mb-4">
    <div class="pos-glass-intro">
        <div class="pos-glass-intro-icon">
            <i class="icon-base ti tabler-receipt" aria-hidden="true"></i>
        </div>
        <div class="pos-glass-intro-content">
            <div class="pos-glass-intro-title">
                <h4 class="mb-1 text-heading fw-bold">Direct Site Expenses</h4>
                <span class="badge bg-label-warning">Materials & Overhead</span>
            </div>
            <p class="pos-glass-intro-subtitle mb-0">Track raw building materials (cement, bricks, steel), equipment rentals, fuel, and utility bills.</p>
        </div>
        <div class="pos-glass-intro-actions">
            <a href="{{ route('expenses.create') }}" class="btn btn-primary">
                <i class="icon-base ti tabler-plus me-1"></i> Add Expense
            </a>
        </div>
    </div>
</div>

{{-- Filter Toolbar --}}
<div class="pos-glass-card pos-tone-secondary mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('expenses.index') }}" class="row g-3 align-items-end">
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
                <label class="form-label small">Category</label>
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
                <label class="form-label small">Payment Method</label>
                <select name="payment_method" class="form-select">
                    <option value="">All Methods</option>
                    @foreach($paymentMethods as $m)
                        <option value="{{ $m }}" {{ request('payment_method') == $m ? 'selected' : '' }}>{{ $m }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small">From Date</label>
                <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
                <a href="{{ route('expenses.index') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

{{-- Total Banner --}}
<div class="pos-glass-card pos-tone-primary mb-4">
    <div class="card-body p-3 d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center">
            <span class="avatar avatar-md bg-label-primary me-3">
                <i class="icon-base ti tabler-receipt fs-4"></i>
            </span>
            <div>
                <div class="small text-muted">Filtered Total Direct Expenses</div>
                <div class="fs-4 fw-bold text-primary">PKR {{ number_format($totalExpense, 0) }}</div>
            </div>
        </div>
        <span class="badge bg-label-primary fs-6">{{ $expenses->total() }} Records</span>
    </div>
</div>

<div class="pos-listing">
    <div class="pos-glass-card pos-tone-secondary pos-listing-panel">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Project</th>
                        <th>Category</th>
                        <th>Vendor / Description</th>
                        <th>Payment Mode</th>
                        <th>Receipt</th>
                        <th class="text-end">Amount</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($expenses as $exp)
                        <tr>
                            <td>{{ $exp->expense_date->format('d M Y') }}</td>
                            <td>
                                <a href="{{ route('projects.show', $exp->project) }}" class="fw-semibold text-heading text-decoration-none">
                                    {{ $exp->project?->name }}
                                </a>
                            </td>
                            <td>
                                <span class="badge" style="background-color: {{ $exp->category?->color }}20; color: {{ $exp->category?->color }};">
                                    <i class="icon-base ti {{ $exp->category?->icon ?? 'tabler-receipt' }} me-1"></i>{{ $exp->category?->name ?? 'Uncategorized' }}
                                </span>
                            </td>
                            <td>
                                <div class="fw-semibold text-heading">{{ $exp->vendor ?? '—' }}</div>
                                <div class="small text-muted">{{ Str::limit($exp->description, 40) }}</div>
                            </td>
                            <td><span class="badge bg-label-secondary">{{ $exp->payment_method ?? 'Cash' }}</span></td>
                            <td>
                                @if($exp->receipt_path)
                                    <a href="{{ asset('storage/' . $exp->receipt_path) }}" target="_blank" class="btn btn-xs btn-outline-info">
                                        <i class="icon-base ti tabler-paperclip"></i> View
                                    </a>
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                            <td class="text-end fw-bold text-dark">
                                PKR {{ number_format($exp->amount, 0) }}
                            </td>
                            <td class="text-end">
                                <a href="{{ route('expenses.edit', $exp) }}" class="btn btn-sm btn-icon btn-outline-secondary" title="Edit">
                                    <i class="icon-base ti tabler-edit"></i>
                                </a>
                                <form action="{{ route('expenses.destroy', $exp) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Delete this expense record?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-icon btn-outline-danger" title="Delete">
                                        <i class="icon-base ti tabler-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="icon-base ti tabler-receipt-off fs-1 d-block mb-2 opacity-50"></i>
                                <p class="mb-2">No expenses recorded matching your criteria.</p>
                                <a href="{{ route('expenses.create') }}" class="btn btn-sm btn-primary">Add Expense</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($expenses->hasPages())
            <div class="card-footer p-3">
                {{ $expenses->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
