@extends('layouts.app')

@section('title', 'Payment Voucher #' . str_pad($payment->id, 5, '0', STR_PAD_LEFT) . ' — ' . config('app.name'))

@section('content')
{{-- Action Header / Glass Intro --}}
<div class="awt-glass-card awt-tone-primary mb-4 d-print-none">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="avatar avatar-lg rounded-3 bg-label-primary d-flex align-items-center justify-content-center">
                <i class="icon-base ti tabler-receipt fs-2"></i>
            </div>
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <h4 class="awt-dash-title mb-0">Payment Voucher #{{ str_pad($payment->id, 5, '0', STR_PAD_LEFT) }}</h4>
                    @if($payment->is_voided)
                        <span class="badge bg-label-danger"><i class="icon-base ti tabler-ban me-1"></i> Voided</span>
                    @else
                        <span class="badge bg-label-success"><i class="icon-base ti tabler-circle-check me-1"></i> Disbursed</span>
                    @endif
                    <span class="badge bg-label-info">{{ $payment->payment_type->label() }}</span>
                </div>
                <div class="awt-dash-subtitle mb-0">
                    Payee: <strong>{{ $payment->contractor?->name }}</strong> &bull;
                    Project: <strong>{{ $payment->project?->name }}</strong> &bull;
                    Disbursed on {{ $payment->payment_date->format('d M Y') }}
                </div>
            </div>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <button type="button" class="btn btn-primary" onclick="window.print();">
                <i class="icon-base ti tabler-printer me-1"></i> Print Voucher
            </button>
            <a href="{{ route('contractor-payments.index') }}" class="btn btn-outline-secondary">
                <i class="icon-base ti tabler-arrow-left me-1"></i> All Payments
            </a>
            @if(auth()->user()->isOwner() || auth()->user()->isAdmin())
                @if(!$payment->is_voided)
                    <form action="{{ route('contractor-payments.destroy', $payment) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Are you sure you want to void this payment voucher? This cannot be undone.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger">
                            <i class="icon-base ti tabler-trash me-1"></i> Void Voucher
                        </button>
                    </form>
                @endif
            @endif
        </div>
    </div>
</div>

{{-- Financial KPI Ribbon (Interactive Web View) --}}
<div class="row g-3 mb-4 d-print-none">
    <div class="col-sm-6 col-xl-3">
        <div class="awt-kpi-card p-3 h-100">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="awt-kpi-title">Voucher Disbursed</span>
                <div class="avatar avatar-sm bg-label-success rounded-circle d-flex align-items-center justify-content-center">
                    <i class="icon-base ti tabler-cash"></i>
                </div>
            </div>
            <div class="awt-kpi-value fs-5 fw-bold text-success mb-1">PKR {{ number_format($payment->amount, 2) }}</div>
            <div class="awt-kpi-footer text-muted">{{ $payment->payment_type->label() }}</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="awt-kpi-card p-3 h-100">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="awt-kpi-title">Contract Value</span>
                <div class="avatar avatar-sm bg-label-primary rounded-circle d-flex align-items-center justify-content-center">
                    <i class="icon-base ti tabler-file-dollar"></i>
                </div>
            </div>
            <div class="awt-kpi-value fs-5 fw-bold text-body mb-1">PKR {{ number_format($contractAmount, 0) }}</div>
            <div class="awt-kpi-footer text-muted">Total agreed scope</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="awt-kpi-card p-3 h-100">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="awt-kpi-title">Total Paid to Date</span>
                <div class="avatar avatar-sm bg-label-info rounded-circle d-flex align-items-center justify-content-center">
                    <i class="icon-base ti tabler-check"></i>
                </div>
            </div>
            <div class="awt-kpi-value fs-5 fw-bold text-info mb-1">PKR {{ number_format($totalPaidToDate, 0) }}</div>
            <div class="awt-kpi-footer text-muted">Including this voucher</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="awt-kpi-card p-3 h-100">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="awt-kpi-title">Remaining Balance</span>
                <div class="avatar avatar-sm bg-label-warning rounded-circle d-flex align-items-center justify-content-center">
                    <i class="icon-base ti tabler-scale"></i>
                </div>
            </div>
            <div class="awt-kpi-value fs-5 fw-bold {{ $remainingBalance > 0 ? 'text-warning' : 'text-success' }} mb-1">
                PKR {{ number_format($remainingBalance, 0) }}
            </div>
            <div class="awt-kpi-footer text-muted">
                @if($contractAmount > 0)
                    {{ round(($totalPaidToDate / $contractAmount) * 100, 1) }}% paid
                @else
                    No contract cap
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Voucher Printable Document Sheet --}}
<div class="row justify-content-center">
    <div class="col-lg-10 col-xl-9">
        <div class="voucher-document card border-0 shadow-sm p-4 p-md-5 position-relative">
            @if($payment->is_voided)
                <div class="voucher-void-watermark">VOIDED</div>
                <div class="alert alert-danger text-center fw-bold py-2 mb-4 d-print-block">
                    <i class="icon-base ti tabler-ban me-1"></i> THIS PAYMENT VOUCHER HAS BEEN OFFICIALLY VOIDED / CANCELLED
                </div>
            @endif

            {{-- Document Brand Header --}}
            <div class="d-flex flex-wrap justify-content-between align-items-start border-bottom pb-4 mb-4 gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <div class="avatar avatar-md rounded-3 bg-label-primary d-flex align-items-center justify-content-center">
                            <i class="icon-base ti tabler-building-skyscraper fs-3"></i>
                        </div>
                        <div>
                            <h3 class="fw-bold text-dark mb-0 letter-spacing-tight">{{ config('app.name', 'Construction Ready') }}</h3>
                            <div class="text-muted small text-uppercase fw-semibold">Disbursement &amp; Payment Voucher</div>
                        </div>
                    </div>
                </div>
                <div class="text-md-end">
                    <div class="badge bg-label-primary fs-6 px-3 py-2 mb-1">
                        VOUCHER #{{ str_pad($payment->id, 5, '0', STR_PAD_LEFT) }}
                    </div>
                    <div class="text-muted small"><strong>Issue Date:</strong> {{ $payment->payment_date->format('d M Y') }}</div>
                    @if($payment->reference)
                        <div class="text-muted small"><strong>Ref No:</strong> <code>{{ $payment->reference }}</code></div>
                    @endif
                </div>
            </div>

            {{-- Parties Cards --}}
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="party-box p-3 rounded-3 border bg-light h-100">
                        <div class="text-muted small text-uppercase fw-bold mb-2 text-primary d-flex align-items-center gap-1">
                            <i class="icon-base ti tabler-user-check"></i> Paid To (Contractor / Payee)
                        </div>
                        <h5 class="fw-bold mb-1 text-dark">{{ $payment->contractor?->name }}</h5>
                        @if($payment->contractor?->company_name)
                            <div class="text-secondary fw-semibold small mb-2">{{ $payment->contractor->company_name }}</div>
                        @endif
                        <div class="row g-1 small text-muted">
                            <div class="col-sm-4 fw-semibold">Phone:</div>
                            <div class="col-sm-8 text-dark">{{ $payment->contractor?->phone ?? '—' }}</div>
                            <div class="col-sm-4 fw-semibold">CNIC:</div>
                            <div class="col-sm-8 text-dark">{{ $payment->contractor?->cnic ?? '—' }}</div>
                            @if($payment->contractor?->address)
                                <div class="col-sm-4 fw-semibold">Address:</div>
                                <div class="col-sm-8 text-dark">{{ $payment->contractor->address }}</div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="party-box p-3 rounded-3 border bg-light h-100">
                        <div class="text-muted small text-uppercase fw-bold mb-2 text-primary d-flex align-items-center gap-1">
                            <i class="icon-base ti tabler-map-pin"></i> Project &amp; Disbursing Site
                        </div>
                        <h5 class="fw-bold mb-1 text-dark">{{ $payment->project?->name }}</h5>
                        <div class="text-secondary fw-semibold small mb-2">{{ $payment->project?->location ?? 'General Project Site' }}</div>
                        <div class="row g-1 small text-muted">
                            <div class="col-sm-4 fw-semibold">Client/Owner:</div>
                            <div class="col-sm-8 text-dark">{{ $payment->project?->owner?->name ?? '—' }}</div>
                            <div class="col-sm-4 fw-semibold">Disbursed By:</div>
                            <div class="col-sm-8 text-dark">{{ $payment->recordedBy?->name ?? 'Administrator' }}</div>
                            <div class="col-sm-4 fw-semibold">Recorded At:</div>
                            <div class="col-sm-8 text-dark">{{ $payment->created_at->format('d M Y, h:i A') }}</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Voucher Particulars Table --}}
            <div class="table-responsive mb-4">
                <table class="table table-bordered align-middle voucher-table">
                    <thead class="table-light text-uppercase small text-muted">
                        <tr>
                            <th style="width: 50%;">Description / Particulars</th>
                            <th class="text-center" style="width: 20%;">Payment Method</th>
                            <th class="text-center" style="width: 15%;">Reference</th>
                            <th class="text-end" style="width: 15%;">Disbursed Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="py-3">
                                <div class="fw-bold text-dark">Contractor Milestone &amp; Service Disbursement</div>
                                <div class="small text-muted mt-1">
                                    {{ $payment->notes ?: 'Disbursement processed for verified site progress and agreed contractual works.' }}
                                </div>
                            </td>
                            <td class="text-center py-3">
                                <span class="badge bg-label-info text-capitalize">{{ $payment->payment_type->label() }}</span>
                            </td>
                            <td class="text-center py-3">
                                @if($payment->reference)
                                    <code class="fw-semibold">{{ $payment->reference }}</code>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-end py-3">
                                <span class="fs-5 fw-bold text-success">PKR {{ number_format($payment->amount, 2) }}</span>
                            </td>
                        </tr>
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <td colspan="3" class="text-end fw-bold text-uppercase small">Total Net Disbursed:</td>
                            <td class="text-end fw-bold fs-5 text-dark">PKR {{ number_format($payment->amount, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- Contract Snapshot Card --}}
            <div class="financial-snapshot-card p-3 rounded-3 border bg-light mb-4">
                <div class="row text-center g-3">
                    <div class="col-4">
                        <div class="small text-muted text-uppercase fw-semibold">Agreed Contract Value</div>
                        <div class="fw-bold text-dark fs-6 mt-1">PKR {{ number_format($contractAmount, 0) }}</div>
                    </div>
                    <div class="col-4 border-start border-end">
                        <div class="small text-muted text-uppercase fw-semibold">Total Paid to Date</div>
                        <div class="fw-bold text-info fs-6 mt-1">PKR {{ number_format($totalPaidToDate, 0) }}</div>
                    </div>
                    <div class="col-4">
                        <div class="small text-muted text-uppercase fw-semibold">Outstanding Balance</div>
                        <div class="fw-bold {{ $remainingBalance > 0 ? 'text-warning' : 'text-success' }} fs-6 mt-1">
                            PKR {{ number_format($remainingBalance, 0) }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- Signatures Section --}}
            <div class="signature-grid pt-5 mt-3">
                <div class="row text-center g-4">
                    <div class="col-4">
                        <div class="sig-line border-top pt-2">
                            <div class="fw-bold text-dark small">{{ $payment->recordedBy?->name ?? 'Disbursement Officer' }}</div>
                            <div class="text-muted extra-small">Prepared / Recorded By</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="sig-line border-top pt-2">
                            <div class="fw-bold text-dark small">{{ $payment->project?->owner?->name ?? 'Project Manager' }}</div>
                            <div class="text-muted extra-small">Verified / Authorized By</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="sig-line border-top pt-2">
                            <div class="fw-bold text-dark small">{{ $payment->contractor?->name }}</div>
                            <div class="text-muted extra-small">Payee Signature &amp; Stamp</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Footer Note --}}
            <div class="text-center text-muted small mt-5 pt-3 border-top d-print-block">
                This is a computer-generated voucher issued by {{ config('app.name', 'Construction Ready') }} on {{ now()->format('d M Y, h:i A') }}.
            </div>
        </div>
    </div>
</div>

<style>
.voucher-document {
    background: #ffffff;
    border-radius: 12px;
}
.letter-spacing-tight {
    letter-spacing: -0.5px;
}
.party-box {
    background-color: rgba(var(--bs-light-rgb), 0.6) !important;
}
.extra-small {
    font-size: 0.75rem;
}
.voucher-void-watermark {
    position: absolute;
    top: 35%;
    left: 50%;
    transform: translate(-50%, -50%) rotate(-25deg);
    font-size: 6rem;
    font-weight: 900;
    color: rgba(220, 53, 69, 0.12);
    letter-spacing: 12px;
    pointer-events: none;
    user-select: none;
    z-index: 10;
}
@media print {
    body {
        background: #ffffff !important;
    }
    .layout-navbar,
    .layout-menu,
    .content-footer,
    .d-print-none {
        display: none !important;
    }
    .layout-page {
        padding: 0 !important;
    }
    .content-wrapper {
        padding: 0 !important;
    }
    .voucher-document {
        box-shadow: none !important;
        border: 1px solid #dee2e6 !important;
        padding: 1.5rem !important;
    }
}
</style>
@endsection

