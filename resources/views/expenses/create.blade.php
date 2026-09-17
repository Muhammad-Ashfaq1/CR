@extends('layouts.app')

@section('title', 'Add Expense — ' . config('app.name'))

@section('content')
<div class="cst-page-header">
    <div>
        <h1 class="cst-page-title">Record Project Expense</h1>
        <p class="cst-page-subtitle">Record material purchases, machinery rentals, fuel, utilities, or site petty cash.</p>
    </div>
    <div>
        <a href="{{ route('expenses.index') }}" class="btn btn-outline-secondary">
            &larr; Back to Expenses
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="cst-card">
            <div class="cst-card-body p-4">
                <form action="{{ route('expenses.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label required">Select Project</label>
                            <select name="project_id" class="form-select @error('project_id') is-invalid @enderror" required>
                                <option value="">Choose Project...</option>
                                @foreach($projects as $p)
                                    <option value="{{ $p->id }}" {{ old('project_id', $selectedProjectId) == $p->id ? 'selected' : '' }}>
                                        {{ $p->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('project_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label required">Expense Category</label>
                            <select name="expense_category_id" class="form-select @error('expense_category_id') is-invalid @enderror" required>
                                <option value="">Select Category...</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ old('expense_category_id') == $cat->id ? 'selected' : '' }}>
                                        {{ $cat->name }} ({{ ucfirst($cat->type) }})
                                    </option>
                                @endforeach
                            </select>
                            @error('expense_category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label required">Amount (PKR)</label>
                            <input type="number" step="0.01" name="amount" class="form-control fs-5 fw-bold text-primary @error('amount') is-invalid @enderror" value="{{ old('amount') }}" placeholder="e.g. 85000" required>
                            @error('amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label required">Expense Date</label>
                            <input type="date" name="expense_date" class="form-control @error('expense_date') is-invalid @enderror" value="{{ old('expense_date', date('Y-m-d')) }}" required>
                            @error('expense_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Vendor / Supplier / Store</label>
                            <input type="text" name="vendor" class="form-control @error('vendor') is-invalid @enderror" value="{{ old('vendor') }}" placeholder="e.g. Lucky Cement Agency, Al-Rehman Hardware">
                            @error('vendor') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Payment Method</label>
                            <select name="payment_method" class="form-select">
                                @foreach($paymentMethods as $pm)
                                    <option value="{{ $pm }}" {{ old('payment_method', 'Cash') === $pm ? 'selected' : '' }}>{{ $pm }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Itemized Description / Bill Details</label>
                            <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="2" placeholder="e.g. 100 Bags OPC Cement @ Rs 1350/bag delivered to site">{{ old('description') }}</textarea>
                            @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Upload Receipt / Bill Image (Optional)</label>
                            <input type="file" name="receipt" class="form-control @error('receipt') is-invalid @enderror" accept="image/*,.pdf">
                            @error('receipt') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Internal Notes / Memo</label>
                            <input type="text" name="notes" class="form-control" value="{{ old('notes') }}" placeholder="e.g. Verified by Site Engineer">
                        </div>

                        <div class="col-12 text-end border-top pt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-check me-1"></i> Save Expense
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
