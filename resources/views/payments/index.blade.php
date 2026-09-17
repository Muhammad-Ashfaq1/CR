@extends('layouts.app')

@section('title', 'Contractor Payments — ' . config('app.name'))

@section('content')
{{-- Banner Intro --}}
<div class="awt-glass-card awt-tone-success mb-4">
    <div class="awt-glass-intro">
        <div class="awt-glass-intro-copy">
            <h4 class="awt-glass-intro-title mb-1">
                <i class="icon-base ti tabler-cash me-2 text-success"></i> Contractor Payment Vouchers
            </h4>
            <p class="awt-glass-intro-subtitle mb-0">Record and audit payments, advances, milestone installments, and printable disbursement vouchers.</p>
        </div>
        <div class="awt-glass-intro-actions">
            @if(auth()->user()->isOwner() || auth()->user()->isAdmin())
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createPaymentModal">
                    <i class="icon-base ti tabler-plus me-1"></i> Record Payment
                </button>
            @endif
        </div>
    </div>
</div>

{{-- Total Paid Glass Metric --}}
<div class="card awt-glass-card awt-tone-success mb-4">
    <div class="awt-stat-body d-flex flex-wrap align-items-center justify-content-between p-3 gap-3">
        <div class="d-flex align-items-center">
            <span class="awt-stat-icon me-3" aria-hidden="true">
                <i class="icon-base ti tabler-cash"></i>
            </span>
            <div>
                <div class="small text-muted text-uppercase fw-semibold">Filtered Total Disbursements</div>
                <div class="fs-4 fw-bold text-success">PKR {{ number_format($totalPaid, 0) }}</div>
            </div>
        </div>
        <span class="badge bg-label-success fs-6">{{ $payments->total() }} Payment Vouchers</span>
    </div>
</div>

<div class="awt-table-card awt-tone-secondary mb-4">
    {{-- Filter Toolbar --}}
    <div class="awt-listing-filter-strip">
        <form method="GET" action="{{ route('contractor-payments.index') }}" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Project</label>
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
                <label class="form-label small fw-semibold">Contractor</label>
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
                <label class="form-label small fw-semibold">Type</label>
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
                <label class="form-label small fw-semibold">From Date</label>
                <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="icon-base ti tabler-filter me-1"></i> Filter
                </button>
                <a href="{{ route('contractor-payments.index') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
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
                            <a href="{{ route('projects.show', $payment->project) }}" class="text-primary text-decoration-none fw-semibold">
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
                            <div class="small text-muted"><code>{{ $payment->reference ?? '—' }}</code></div>
                        </td>
                        <td class="text-end fw-bold text-success">
                            PKR {{ number_format($payment->amount, 0) }}
                        </td>
                        <td class="text-end">
                            <div class="d-inline-flex gap-1 align-items-center">
                                <a href="{{ route('contractor-payments.show', $payment) }}" class="btn btn-sm btn-outline-primary" title="View Voucher">
                                    <i class="icon-base ti tabler-file-text me-1"></i> Voucher &rarr;
                                </a>
                                @if(auth()->user()->isAdmin() || auth()->user()->isOwner())
                                    <form action="{{ route('contractor-payments.destroy', $payment) }}" method="POST" class="d-inline-block form-void-payment">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-sm btn-icon btn-text-danger rounded-pill btn-void-trigger" title="Void Voucher">
                                            <i class="icon-base ti tabler-ban"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">
                            <div class="awt-empty-state">
                                <span class="awt-empty-state-icon">
                                    <i class="icon-base ti tabler-receipt-off"></i>
                                </span>
                                <h6 class="awt-empty-state-title">No payment vouchers found</h6>
                                <p class="awt-empty-state-desc">No contractor payments recorded matching your filter parameters.</p>
                                @if(auth()->user()->isOwner() || auth()->user()->isAdmin())
                                    <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#createPaymentModal">
                                        <i class="icon-base ti tabler-plus me-1"></i> Record Payment
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($payments->hasPages())
        <div class="card-footer">
            {{ $payments->links() }}
        </div>
    @endif
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.btn-void-trigger').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            var form = this.closest('form');
            if (typeof confirmDelete === 'function') {
                confirmDelete(function(confirmed) {
                    if (confirmed) form.submit();
                }, 'Are you sure you want to void this payment voucher? This will reverse the transaction balance.', 'Void Voucher?');
            } else if (confirm('Are you sure you want to void this payment voucher?')) {
                form.submit();
            }
        });
    });
});
</script>
@endpush

{{-- Record Payment Modal --}}
@if(auth()->user()->isOwner() || auth()->user()->isAdmin())
<div class="modal fade" id="createPaymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title text-white"><i class="icon-base ti tabler-cash me-2"></i> Record Contractor Payment Voucher</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('contractor-payments.store') }}" method="POST">
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
                            <label class="form-label required fw-semibold">Select Contractor</label>
                            <select name="contractor_id" class="form-select" required>
                                <option value="">Choose Contractor...</option>
                                @foreach($contractors as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->company_name ?? 'Individual' }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required fw-semibold">Payment Amount (PKR)</label>
                            <input type="number" step="0.01" name="amount" class="form-control fs-5 fw-bold text-success" placeholder="e.g. 150000" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required fw-semibold">Payment Date</label>
                            <input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required fw-semibold">Payment Type</label>
                            <select name="payment_type" class="form-select" required>
                                @foreach($paymentTypes as $type)
                                    <option value="{{ $type->value }}" {{ $type->value === 'installment' ? 'selected' : '' }}>
                                        {{ $type->label() }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Reference / Cheque / Tx ID</label>
                            <input type="text" name="reference" class="form-control" placeholder="e.g. Cheque #49281 or Online Ref">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Notes / Payment Description</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="e.g. Paid for ground floor slab completion..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success"><i class="icon-base ti tabler-check me-1"></i> Record & Generate Voucher</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@push('scripts')
@if(request('action') === 'create')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const m = document.getElementById('createPaymentModal');
        if (m) new bootstrap.Modal(m).show();
    });
</script>
@endif
@endpush
@endsection
