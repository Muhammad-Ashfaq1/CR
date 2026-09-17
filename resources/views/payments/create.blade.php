@extends('layouts.app')

@section('title', 'Make Contractor Payment — ' . config('app.name'))

@section('content')
<div class="cst-page-header">
    <div>
        <h1 class="cst-page-title">Record Contractor Payment</h1>
        <p class="cst-page-subtitle">Disburse contract advances, milestone installments, or final settlement payments.</p>
    </div>
    <div>
        <a href="{{ route('contractor-payments.index') }}" class="btn btn-outline-secondary">
            &larr; Back to Payments
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="cst-card">
            <div class="cst-card-body p-4">
                <form action="{{ route('contractor-payments.store') }}" method="POST">
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
                            <label class="form-label required">Select Contractor</label>
                            <select name="contractor_id" class="form-select @error('contractor_id') is-invalid @enderror" required>
                                <option value="">Choose Contractor...</option>
                                @foreach($contractors as $c)
                                    <option value="{{ $c->id }}" {{ old('contractor_id', $selectedContractorId) == $c->id ? 'selected' : '' }}>
                                        {{ $c->name }} ({{ $c->company_name ?? 'Individual' }})
                                    </option>
                                @endforeach
                            </select>
                            @error('contractor_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label required">Payment Amount (PKR)</label>
                            <input type="number" step="0.01" name="amount" class="form-control fs-5 fw-bold text-success @error('amount') is-invalid @enderror" value="{{ old('amount') }}" placeholder="e.g. 150000" required>
                            @error('amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label required">Payment Date</label>
                            <input type="date" name="payment_date" class="form-control @error('payment_date') is-invalid @enderror" value="{{ old('payment_date', date('Y-m-d')) }}" required>
                            @error('payment_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label required">Payment Type</label>
                            <select name="payment_type" class="form-select @error('payment_type') is-invalid @enderror" required>
                                @foreach($paymentTypes as $type)
                                    <option value="{{ $type->value }}" {{ old('payment_type', 'installment') === $type->value ? 'selected' : '' }}>
                                        {{ $type->label() }}
                                    </option>
                                @endforeach
                            </select>
                            @error('payment_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Reference / Cheque / Tx ID</label>
                            <input type="text" name="reference" class="form-control" value="{{ old('reference') }}" placeholder="e.g. Cheque #49281 or Online Ref">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Notes / Payment Description</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="e.g. Paid for ground floor lintel beam completion...">{{ old('notes') }}</textarea>
                        </div>

                        <div class="col-12 text-end border-top pt-3">
                            <button type="submit" class="btn btn-success">
                                <i class="ti ti-check me-1"></i> Record Payment & Generate Voucher
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
