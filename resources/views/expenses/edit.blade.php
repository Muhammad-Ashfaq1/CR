@extends('layouts.app')

@section('title', 'Edit Expense — ' . config('app.name'))

@section('content')
<div class="pos-glass-intro pos-tone-primary mb-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <h4 class="pos-glass-intro-title mb-1">
                <i class="icon-base ti tabler-edit me-2 text-primary"></i> Edit Expense #{{ $expense->id }}
            </h4>
            <div class="pos-glass-intro-sub">Update project expense amount, category, or receipt voucher.</div>
        </div>
        <div>
            <a href="{{ route('expenses.index') }}" class="btn btn-outline-secondary">
                <i class="icon-base ti tabler-arrow-left me-1"></i> Back to Expenses
            </a>
        </div>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <form action="{{ route('expenses.update', $expense) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label required fw-semibold">Select Project</label>
                            <select name="project_id" class="form-select @error('project_id') is-invalid @enderror" required>
                                @foreach($projects as $p)
                                    <option value="{{ $p->id }}" {{ old('project_id', $expense->project_id) == $p->id ? 'selected' : '' }}>
                                        {{ $p->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('project_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label required fw-semibold">Expense Category</label>
                            <select name="expense_category_id" class="form-select @error('expense_category_id') is-invalid @enderror" required>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ old('expense_category_id', $expense->expense_category_id) == $cat->id ? 'selected' : '' }}>
                                        {{ $cat->name }} ({{ ucfirst($cat->type) }})
                                    </option>
                                @endforeach
                            </select>
                            @error('expense_category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label required fw-semibold">Amount (PKR)</label>
                            <input type="number" step="0.01" name="amount" class="form-control fs-5 fw-bold text-primary @error('amount') is-invalid @enderror" value="{{ old('amount', $expense->amount) }}" required>
                            @error('amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label required fw-semibold">Expense Date</label>
                            <input type="date" name="expense_date" class="form-control @error('expense_date') is-invalid @enderror" value="{{ old('expense_date', $expense->expense_date?->format('Y-m-d')) }}" required>
                            @error('expense_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Vendor / Supplier / Store</label>
                            <input type="text" name="vendor" class="form-control @error('vendor') is-invalid @enderror" value="{{ old('vendor', $expense->vendor) }}">
                            @error('vendor') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Payment Method</label>
                            <select name="payment_method" class="form-select">
                                @foreach($paymentMethods as $pm)
                                    <option value="{{ $pm }}" {{ old('payment_method', $expense->payment_method) === $pm ? 'selected' : '' }}>{{ $pm }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Itemized Description / Bill Details</label>
                            <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="2">{{ old('description', $expense->description) }}</textarea>
                            @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Replace Receipt / Bill File</label>
                            <input type="file" name="receipt" class="form-control" accept="image/*,.pdf">
                            @if($expense->receipt_path)
                                <div class="small text-muted mt-1">
                                    Current: <a href="{{ asset('storage/' . $expense->receipt_path) }}" target="_blank">View existing receipt</a>
                                </div>
                            @endif
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Internal Notes / Memo</label>
                            <input type="text" name="notes" class="form-control" value="{{ old('notes', $expense->notes) }}">
                        </div>

                        <div class="col-12 text-end border-top pt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="icon-base ti tabler-device-floppy me-1"></i> Save Changes
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
