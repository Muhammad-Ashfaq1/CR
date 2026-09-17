@extends('layouts.app')

@section('title', 'Contractor Payments — ' . config('app.name'))

@section('content')
<div class="cst-page-header">
    <div>
        <h1 class="cst-page-title">Contractor Payments</h1>
        <p class="cst-page-subtitle">Record and audit payments, installments, and advances made to project contractors.</p>
    </div>
    <div>
        @if(auth()->user()->isOwner() || auth()->user()->isAdmin())
            <a href="{{ route('contractor-payments.create') }}" class="btn btn-primary">
                <i class="ti ti-plus me-1"></i> Make Payment
            </a>
        @endif
    </div>
</div>

<div class="cst-card mb-4">
    <div class="cst-card-body p-3">
        <form method="GET" action="{{ route('contractor-payments.index') }}" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label small">Project</label>
                <select name="project_id" class="form-select">
                    <option value="">All Projects</option>
                    @foreach($projects as $project)
                        <option value="{{ $project->id }}" {{ request('project_id') == $project->id ? 'selected' : '' }}>
                            {{ $project->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small">Contractor</label>
                <select name="contractor_id" class="form-select">
                    <option value="">All Contractors</option>
                    @foreach($contractors as $contractor)
                        <option value="{{ $contractor->id }}" {{ request('contractor_id') == $contractor->id ? 'selected' : '' }}>
                            {{ $contractor->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small">Type</label>
                <select name="payment_type" class="form-select">
                    <option value="">All Types</option>
                    @foreach($paymentTypes as $type)
                        <option value="{{ $type->value }}" {{ request('payment_type') == $type->value ? 'selected' : '' }}>
                            {{ $type->label() }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small">From Date</label>
                <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
                <a href="{{ route('contractor-payments.index') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

{{-- Total Paid Banner --}}
<div class="alert alert-success d-flex align-items-center justify-content-between p-3 mb-4 rounded-3 border-0 bg-success bg-opacity-10 text-success">
    <div class="d-flex align-items-center">
        <i class="ti ti-cash fs-2 me-3"></i>
        <div>
            <div class="small fw-semibold">Filtered Total Disbursements</div>
            <div class="fs-4 fw-bold">PKR {{ number_format($totalPaid, 0) }}</div>
        </div>
    </div>
    <span class="badge bg-success">{{ $payments->total() }} Payment Vouchers</span>
</div>

<div class="cst-card">
    <div class="cst-card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Voucher #</th>
                        <th>Payment Date</th>
                        <th>Project</th>
                        <th>Contractor</th>
                        <th>Type</th>
                        <th>Reference</th>
                        <th class="text-end">Amount</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $payment)
                        <tr>
                            <td>
                                <a href="{{ route('contractor-payments.show', $payment) }}" class="fw-bold text-primary text-decoration-none">
                                    #{{ str_pad($payment->id, 5, '0', STR_PAD_LEFT) }}
                                </a>
                            </td>
                            <td>{{ $payment->payment_date->format('d M Y') }}</td>
                            <td>
                                <a href="{{ route('projects.show', $payment->project) }}" class="text-dark text-decoration-none fw-semibold">
                                    {{ $payment->project?->name }}
                                </a>
                            </td>
                            <td>
                                <a href="{{ route('contractors.show', $payment->contractor) }}" class="text-dark text-decoration-none">
                                    {{ $payment->contractor?->name }}
                                </a>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">{{ $payment->payment_type->label() }}</span>
                            </td>
                            <td>
                                <div class="small text-muted">{{ $payment->reference ?? '—' }}</div>
                            </td>
                            <td class="text-end fw-bold text-success">
                                PKR {{ number_format($payment->amount, 0) }}
                            </td>
                            <td class="text-end">
                                <a href="{{ route('contractor-payments.show', $payment) }}" class="btn btn-sm btn-outline-primary" title="View Voucher">
                                    <i class="ti ti-file-text"></i> Voucher
                                </a>
                                @if(auth()->user()->isAdmin() || auth()->user()->isOwner())
                                    <form action="{{ route('contractor-payments.destroy', $payment) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Void this payment voucher?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-icon btn-outline-danger" title="Void">
                                            <i class="ti ti-ban"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="ti ti-receipt-off fs-1 d-block mb-2 opacity-50"></i>
                                <p class="mb-2">No contractor payments recorded.</p>
                                @if(auth()->user()->isOwner() || auth()->user()->isAdmin())
                                    <a href="{{ route('contractor-payments.create') }}" class="btn btn-sm btn-primary">Make First Payment</a>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($payments->hasPages())
        <div class="cst-card-footer p-3">
            {{ $payments->links() }}
        </div>
    @endif
</div>
@endsection
