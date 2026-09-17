@extends('layouts.app')

@section('title', 'Payment Voucher #' . str_pad($payment->id, 5, '0', STR_PAD_LEFT) . ' — ' . config('app.name'))

@section('content')
<div class="pos-glass-intro pos-tone-primary mb-4 d-print-none">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <h4 class="pos-glass-intro-title mb-1">
                <i class="icon-base ti tabler-receipt me-2 text-primary"></i> Payment Voucher #{{ str_pad($payment->id, 5, '0', STR_PAD_LEFT) }}
            </h4>
            <div class="pos-glass-intro-sub">Official payment disbursement voucher and project balance snapshot.</div>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary" onclick="window.print();">
                <i class="icon-base ti tabler-printer me-1"></i> Print Voucher
            </button>
            <a href="{{ route('contractor-payments.index') }}" class="btn btn-outline-primary">
                <i class="icon-base ti tabler-arrow-left me-1"></i> Back to Payments
            </a>
        </div>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="card border-0 shadow-sm p-5">
            @if($payment->is_voided)
                <div class="alert alert-danger text-center fw-bold py-2 mb-4">
                    <i class="icon-base ti tabler-ban me-1"></i> THIS PAYMENT VOUCHER HAS BEEN VOIDED / CANCELLED
                </div>
            @endif

            {{-- Voucher Header --}}
            <div class="d-flex justify-content-between align-items-start border-bottom pb-4 mb-4">
                <div>
                    <h3 class="fw-bold text-dark mb-1"><i class="icon-base ti tabler-building-skyscraper text-primary me-2"></i>{{ config('app.name', 'Construction Ready') }}</h3>
                    <div class="text-muted">Payment Disbursement Voucher</div>
                </div>
                <div class="text-end">
                    <div class="fs-5 fw-bold text-primary">VOUCHER #{{ str_pad($payment->id, 5, '0', STR_PAD_LEFT) }}</div>
                    <div class="text-muted">Date: {{ $payment->payment_date->format('d M Y') }}</div>
                </div>
            </div>

            {{-- Parties Row --}}
            <div class="row mb-4">
                <div class="col-6">
                    <div class="text-muted small text-uppercase fw-bold mb-1">Paid To (Contractor)</div>
                    <h5 class="fw-bold mb-0 text-dark">{{ $payment->contractor?->name }}</h5>
                    <div class="text-muted">{{ $payment->contractor?->company_name }}</div>
                    <div class="text-muted small">Phone: {{ $payment->contractor?->phone ?? '—' }}</div>
                    <div class="text-muted small">CNIC: {{ $payment->contractor?->cnic ?? '—' }}</div>
                </div>
                <div class="col-6 text-end">
                    <div class="text-muted small text-uppercase fw-bold mb-1">Project Site</div>
                    <h5 class="fw-bold mb-0 text-dark">{{ $payment->project?->name }}</h5>
                    <div class="text-muted">{{ $payment->project?->location ?? '—' }}</div>
                    <div class="text-muted small">Owner: {{ $payment->project?->owner?->name }}</div>
                </div>
            </div>

            {{-- Payment Details Table --}}
            <div class="table-responsive mb-4">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Description</th>
                            <th>Payment Mode / Type</th>
                            <th>Reference No.</th>
                            <th class="text-end">Disbursed Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <div class="fw-semibold">Contractor Milestone Payment</div>
                                <div class="small text-muted">{{ $payment->notes ?? 'Payment recorded for agreed contract services.' }}</div>
                            </td>
                            <td>
                                <span class="badge bg-label-info">{{ $payment->payment_type->label() }}</span>
                            </td>
                            <td><code>{{ $payment->reference ?? '—' }}</code></td>
                            <td class="text-end fs-5 fw-bold text-success">
                                PKR {{ number_format($payment->amount, 2) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Financial Balance Snapshot Box --}}
            <div class="p-3 bg-light rounded border mb-4">
                <div class="row text-center">
                    <div class="col-4">
                        <div class="small text-muted">Total Contract Amount</div>
                        <div class="fw-bold text-dark">PKR {{ number_format($contractAmount, 0) }}</div>
                    </div>
                    <div class="col-4 border-start border-end">
                        <div class="small text-muted">Total Paid to Date</div>
                        <div class="fw-bold text-success">PKR {{ number_format($totalPaidToDate, 0) }}</div>
                    </div>
                    <div class="col-4">
                        <div class="small text-muted">Remaining Balance</div>
                        <div class="fw-bold text-danger">PKR {{ number_format($remainingBalance, 0) }}</div>
                    </div>
                </div>
            </div>

            {{-- Signatures --}}
            <div class="row pt-5 mt-4 text-center">
                <div class="col-4">
                    <div class="border-top pt-2 text-muted small">
                        Recorded by ({{ $payment->recordedBy?->name ?? 'Admin' }})
                    </div>
                </div>
                <div class="col-4">
                    <div class="border-top pt-2 text-muted small">
                        Verified by Project Owner
                    </div>
                </div>
                <div class="col-4">
                    <div class="border-top pt-2 text-muted small">
                        Contractor Signature / Stamp
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
