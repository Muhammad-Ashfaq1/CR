@extends('layouts.app')

@section('title', 'Contractor Payments — ' . config('app.name'))

@section('content')
{{-- Banner Intro --}}
<div class="pos-glass-card pos-tone-success mb-4">
    <div class="pos-glass-intro">
        <div class="pos-glass-intro-icon">
            <i class="icon-base ti tabler-cash" aria-hidden="true"></i>
        </div>
        <div class="pos-glass-intro-content">
            <div class="pos-glass-intro-title">
                <h4 class="mb-1 text-heading fw-bold">Contractor Payment Vouchers</h4>
                <span class="badge bg-label-success">Disbursement Log</span>
            </div>
            <p class="pos-glass-intro-subtitle mb-0">Record and audit payments, advances, milestone installments, and printable disbursement vouchers.</p>
        </div>
        <div class="pos-glass-intro-actions">
            @if(auth()->user()->isOwner() || auth()->user()->isAdmin())
                <a href="{{ route('contractor-payments.create') }}" class="btn btn-success">
                    <i class="icon-base ti tabler-plus me-1"></i> Make Payment
                </a>
            @endif
        </div>
    </div>
</div>

{{-- Filter Toolbar --}}
<div class="pos-glass-card pos-tone-secondary mb-4">
    <div class="card-body p-3">
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

{{-- Total Paid Glass Metric --}}
<div class="pos-glass-card pos-tone-success mb-4">
    <div class="card-body p-3 d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center">
            <span class="avatar avatar-md bg-label-success me-3">
                <i class="icon-base ti tabler-cash fs-4"></i>
            </span>
            <div>
                <div class="small text-muted">Filtered Total Disbursements</div>
                <div class="fs-4 fw-bold text-success">PKR {{ number_format($totalPaid, 0) }}</div>
            </div>
        </div>
        <span class="badge bg-label-success fs-6">{{ $payments->total() }} Payment Vouchers</span>
    </div>
</div>

<div class="pos-listing">
    <div class="pos-glass-card pos-tone-secondary pos-listing-panel">
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
                                <a href="{{ route('projects.show', $payment->project) }}" class="text-heading text-decoration-none fw-semibold">
                                    {{ $payment->project?->name }}
                                </a>
                            </td>
                            <td>
                                <a href="{{ route('contractors.show', $payment->contractor) }}" class="text-heading text-decoration-none">
                                    {{ $payment->contractor?->name }}
                                </a>
                            </td>
                            <td>
                                <span class="badge bg-label-info">{{ $payment->payment_type->label() }}</span>
                            </td>
                            <td>
                                <div class="small text-muted">{{ $payment->reference ?? '—' }}</div>
                            </td>
                            <td class="text-end fw-bold text-success">
                                PKR {{ number_format($payment->amount, 0) }}
                            </td>
                            <td class="text-end">
                                <a href="{{ route('contractor-payments.show', $payment) }}" class="btn btn-sm btn-outline-primary" title="View Voucher">
                                    <i class="icon-base ti tabler-file-text"></i> Voucher
                                </a>
                                @if(auth()->user()->isAdmin() || auth()->user()->isOwner())
                                    <form action="{{ route('contractor-payments.destroy', $payment) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Void this payment voucher?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-icon btn-outline-danger" title="Void">
                                            <i class="icon-base ti tabler-ban"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="icon-base ti tabler-receipt-off fs-1 d-block mb-2 opacity-50"></i>
                                <p class="mb-2">No contractor payments recorded.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($payments->hasPages())
            <div class="card-footer p-3">
                {{ $payments->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
