@extends('layouts.app')

@section('title', 'Expenses — ' . config('app.name'))

@section('content')
{{-- Banner Intro --}}
<div class="pos-glass-intro pos-tone-warning mb-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <h4 class="pos-glass-intro-title mb-1">
                <i class="icon-base ti tabler-receipt me-2 text-warning"></i> Direct Site Expenses
            </h4>
            <div class="pos-glass-intro-sub">Track raw building materials (cement, bricks, steel), equipment rentals, fuel, and utility bills.</div>
        </div>
        <div>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createExpenseModal">
                <i class="icon-base ti tabler-plus me-1"></i> Record Expense
            </button>
        </div>
    </div>
</div>

{{-- Filter Toolbar --}}
<div class="pos-listing-panel mb-4">
    <form method="GET" action="{{ route('expenses.index') }}" class="row g-3 align-items-end">
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
            <label class="form-label small fw-semibold">Payment Method</label>
            <select name="payment_method" class="form-select">
                <option value="">All Methods</option>
                @foreach($paymentMethods as $m)
                    <option value="{{ $m }}" {{ request('payment_method') == $m ? 'selected' : '' }}>{{ $m }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-semibold">From Date</label>
            <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
        </div>
        <div class="col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-primary w-100">
                <i class="icon-base ti tabler-filter me-1"></i> Filter
            </button>
            <a href="{{ route('expenses.index') }}" class="btn btn-outline-secondary">Reset</a>
        </div>
    </form>
</div>

{{-- Total Banner --}}
<div class="pos-glass-card pos-tone-primary mb-4">
    <div class="pos-stat-body d-flex align-items-center justify-content-between p-3">
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
                            <a href="{{ route('projects.show', $exp->project) }}" class="fw-semibold text-primary text-decoration-none">
                                {{ $exp->project?->name }}
                            </a>
                        </td>
                        <td>
                            <span class="badge" style="background-color: {{ $exp->category?->color }}20; color: {{ $exp->category?->color }};">
                                <i class="icon-base ti {{ $exp->category?->icon ?? 'tabler-receipt' }} me-1"></i>{{ $exp->category?->name ?? 'Uncategorized' }}
                            </span>
                        </td>
                        <td>
                            <div class="fw-semibold text-dark">{{ $exp->vendor ?? '—' }}</div>
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
                            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editExpenseModal_{{ $exp->id }}" title="Edit">
                                <i class="icon-base ti tabler-edit"></i>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="icon-base ti tabler-receipt-off fs-1 d-block mb-2 opacity-50"></i>
                            <p class="mb-2">No expenses recorded matching your criteria.</p>
                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#createExpenseModal">
                                Record Expense
                            </button>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($expenses->hasPages())
        <div class="p-3 border-top">
            {{ $expenses->links() }}
        </div>
    @endif
</div>

{{-- Record Expense Modal --}}
<div class="modal fade" id="createExpenseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white"><i class="icon-base ti tabler-receipt me-2"></i> Record Direct Site Expense</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('expenses.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label required fw-semibold">Select Project</label>
                            <select name="project_id" class="form-select" required>
                                <option value="">Choose Project...</option>
                                @foreach($projects as $p)
                                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required fw-semibold">Expense Category</label>
                            <select name="expense_category_id" class="form-select" required>
                                <option value="">Select Category...</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }} ({{ ucfirst($cat->type) }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required fw-semibold">Amount (PKR)</label>
                            <input type="number" step="0.01" name="amount" class="form-control fs-5 fw-bold text-primary" placeholder="e.g. 85000" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required fw-semibold">Expense Date</label>
                            <input type="date" name="expense_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Vendor / Supplier / Store</label>
                            <input type="text" name="vendor" class="form-control" placeholder="e.g. Lucky Cement Agency">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Payment Method</label>
                            <select name="payment_method" class="form-select">
                                @foreach($paymentMethods as $pm)
                                    <option value="{{ $pm }}" {{ $pm === 'Cash' ? 'selected' : '' }}>{{ $pm }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Itemized Description / Bill Details</label>
                            <textarea name="description" class="form-control" rows="2" placeholder="e.g. 100 Bags OPC Cement @ Rs 1350/bag"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Upload Receipt / Bill Image (Optional)</label>
                            <input type="file" name="receipt" class="form-control" accept="image/*,.pdf">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Internal Notes / Memo</label>
                            <input type="text" name="notes" class="form-control" placeholder="e.g. Verified by Site Engineer">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="icon-base ti tabler-check me-1"></i> Save Expense</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Expense Modals --}}
@foreach($expenses as $exp)
<div class="modal fade" id="editExpenseModal_{{ $exp->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white"><i class="icon-base ti tabler-edit me-2"></i> Edit Expense #{{ $exp->id }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('expenses.update', $exp) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label required fw-semibold">Select Project</label>
                            <select name="project_id" class="form-select" required>
                                @foreach($projects as $p)
                                    <option value="{{ $p->id }}" {{ $exp->project_id == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required fw-semibold">Expense Category</label>
                            <select name="expense_category_id" class="form-select" required>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ $exp->expense_category_id == $cat->id ? 'selected' : '' }}>{{ $cat->name }} ({{ ucfirst($cat->type) }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required fw-semibold">Amount (PKR)</label>
                            <input type="number" step="0.01" name="amount" class="form-control fs-5 fw-bold text-primary" value="{{ $exp->amount }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required fw-semibold">Expense Date</label>
                            <input type="date" name="expense_date" class="form-control" value="{{ $exp->expense_date?->format('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Vendor / Supplier / Store</label>
                            <input type="text" name="vendor" class="form-control" value="{{ $exp->vendor }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Payment Method</label>
                            <select name="payment_method" class="form-select">
                                @foreach($paymentMethods as $pm)
                                    <option value="{{ $pm }}" {{ $exp->payment_method === $pm ? 'selected' : '' }}>{{ $pm }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Itemized Description / Bill Details</label>
                            <textarea name="description" class="form-control" rows="2">{{ $exp->description }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Replace Receipt Image (Optional)</label>
                            <input type="file" name="receipt" class="form-control" accept="image/*,.pdf">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Internal Notes / Memo</label>
                            <input type="text" name="notes" class="form-control" value="{{ $exp->notes }}">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-danger" onclick="if(confirm('Delete this expense?')) { document.getElementById('delExpForm_{{ $exp->id }}').submit(); }">
                        <i class="icon-base ti tabler-trash me-1"></i> Delete
                    </button>
                    <div>
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary"><i class="icon-base ti tabler-device-floppy me-1"></i> Save Changes</button>
                    </div>
                </div>
            </form>
            <form id="delExpForm_{{ $exp->id }}" action="{{ route('expenses.destroy', $exp) }}" method="POST" class="d-none">
                @csrf
                @method('DELETE')
            </form>
        </div>
    </div>
</div>
@endforeach
@endsection
