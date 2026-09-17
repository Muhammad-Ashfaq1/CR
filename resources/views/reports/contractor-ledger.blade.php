@extends('layouts.app')

@section('title', 'Contractor Statement & Ledger — ' . config('app.name'))

@section('content')
<div class="cst-page-header">
    <div>
        <h1 class="cst-page-title">Contractor Statement of Account</h1>
        <p class="cst-page-subtitle">Complete ledger of contract commitments, milestone payments, and running balance.</p>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-outline-secondary" onclick="window.print();">
            <i class="ti ti-printer me-1"></i> Print Statement
        </button>
        <a href="{{ route('reports.index') }}" class="btn btn-outline-primary">
            &larr; Reports Hub
        </a>
    </div>
</div>

<div class="cst-card mb-4 d-print-none">
    <div class="cst-card-body p-3">
        <form method="GET" action="{{ route('reports.contractor-ledger') }}" class="row g-3 align-items-end">
            <div class="col-md-6">
                <label class="form-label small fw-semibold">Select Contractor</label>
                <select name="contractor_id" class="form-select" onchange="this.form.submit()">
                    @foreach($contractors as $c)
                        <option value="{{ $c->id }}" {{ $selectedContractorId == $c->id ? 'selected' : '' }}>
                            {{ $c->name }} ({{ $c->company_name ?? 'Individual' }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Filter by Project (Optional)</label>
                <select name="project_id" class="form-select" onchange="this.form.submit()">
                    <option value="">All Assigned Projects</option>
                    @if($contractor)
                        @foreach($contractor->projects as $p)
                            <option value="{{ $p->id }}" {{ $selectedProjectId == $p->id ? 'selected' : '' }}>
                                {{ $p->name }}
                            </option>
                        @endforeach
                    @endif
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Load Ledger</button>
            </div>
        </form>
    </div>
</div>

@if(!$contractor)
    <div class="cst-card text-center py-5 text-muted">
        <i class="ti ti-user-x fs-1 d-block mb-2 opacity-50"></i>
        <h5>No contractor selected.</h5>
    </div>
@else
    {{-- Statement Header Info --}}
    <div class="cst-card p-4 mb-4 border">
        <div class="d-flex justify-content-between align-items-start border-bottom pb-3 mb-3">
            <div>
                <h4 class="fw-bold mb-1 text-dark">{{ $contractor->name }}</h4>
                <div class="text-muted">{{ $contractor->company_name ?? 'Individual Contractor' }} &bull; Phone: {{ $contractor->phone ?? '—' }} &bull; CNIC: {{ $contractor->cnic ?? '—' }}</div>
            </div>
            <div class="text-end">
                <span class="badge bg-light text-dark border p-2">As of {{ date('d M Y') }}</span>
            </div>
        </div>

        <div class="row g-3 text-center">
            <div class="col-md-4">
                <div class="p-3 bg-light rounded">
                    <div class="small text-muted">Total Contract Value</div>
                    <div class="fs-4 fw-bold text-dark">PKR {{ number_format($totalContract, 0) }}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 bg-light rounded">
                    <div class="small text-muted">Total Payments Cleared</div>
                    <div class="fs-4 fw-bold text-success">PKR {{ number_format($totalPaid, 0) }}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 bg-light rounded">
                    <div class="small text-muted">Outstanding Balance Due</div>
                    <div class="fs-4 fw-bold text-danger">PKR {{ number_format($remaining, 0) }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Detailed Ledger Table --}}
    <div class="cst-card">
        <div class="cst-card-header">
            <h5 class="cst-card-title mb-0">Disbursement History & Running Balance</h5>
        </div>
        <div class="cst-card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Project</th>
                            <th>Payment Type</th>
                            <th>Reference / Notes</th>
                            <th class="text-end">Paid Amount (Dr)</th>
                            <th class="text-end">Running Balance Due</th>
                            <th class="text-end d-print-none">Voucher</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="table-secondary">
                            <td colspan="4" class="fw-bold">Initial Contract Total</td>
                            <td class="text-end">—</td>
                            <td class="text-end fw-bold">PKR {{ number_format($totalContract, 2) }}</td>
                            <td class="d-print-none"></td>
                        </tr>
                        @forelse($ledgerEntries as $entry)
                            <tr>
                                <td>{{ $entry['date']->format('d M Y') }}</td>
                                <td class="fw-semibold">{{ $entry['project'] }}</td>
                                <td><span class="badge bg-light text-dark border">{{ $entry['type'] }}</span></td>
                                <td>
                                    <div>{{ $entry['reference'] ?? '—' }}</div>
                                    <div class="small text-muted">{{ $entry['notes'] }}</div>
                                </td>
                                <td class="text-end fw-bold text-success">
                                    PKR {{ number_format($entry['amount'], 2) }}
                                </td>
                                <td class="text-end fw-bold text-danger">
                                    PKR {{ number_format($entry['running_balance'], 2) }}
                                </td>
                                <td class="text-end d-print-none">
                                    <a href="{{ route('contractor-payments.show', $entry['payment']) }}" class="btn btn-xs btn-outline-primary">
                                        #{{ str_pad($entry['payment']->id, 4, '0', STR_PAD_LEFT) }}
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-3 text-muted">No payments disbursed yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="4" class="text-end fw-bold">Final Outstanding Balance:</th>
                            <th class="text-end fw-bold text-success">PKR {{ number_format($totalPaid, 2) }}</th>
                            <th class="text-end fw-bold fs-6 text-danger">PKR {{ number_format($remaining, 2) }}</th>
                            <th class="d-print-none"></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
@endif
@endsection
