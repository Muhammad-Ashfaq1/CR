@extends('layouts.app')

@section('title', 'Contractor Statement & Ledger — ' . config('app.name'))

@section('content')
<div class="awt-glass-card awt-tone-primary mb-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="avatar avatar-lg rounded-3 bg-label-primary d-flex align-items-center justify-content-center">
                <i class="icon-base ti tabler-file-analytics fs-2"></i>
            </div>
            <div>
                <h4 class="awt-dash-title mb-1">Contractor Statement of Account</h4>
                <p class="awt-dash-subtitle mb-0">Complete ledger of contract commitments, milestone payments, and running balance.</p>
            </div>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary" onclick="window.print();">
                <i class="icon-base ti tabler-printer me-1"></i> Print Statement
            </button>
            <a href="{{ route('reports.index') }}" class="btn btn-outline-primary">
                <i class="icon-base ti tabler-arrow-left me-1"></i> Reports Hub
            </a>
        </div>
    </div>
</div>

<div class="awt-listing-filter-strip awt-tone-secondary mb-4 d-print-none">
    <form method="GET" action="{{ route('reports.contractor-ledger') }}" class="row g-3 align-items-end">
        <div class="col-md-6">
            <label class="form-label small fw-semibold text-muted text-uppercase">Select Contractor</label>
            <select name="contractor_id" class="form-select" onchange="this.form.submit()">
                @foreach($contractors as $c)
                    <option value="{{ $c->id }}" {{ $selectedContractorId == $c->id ? 'selected' : '' }}>
                        {{ $c->name }} ({{ $c->company_name ?? 'Individual' }})
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label small fw-semibold text-muted text-uppercase">Filter by Project (Optional)</label>
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

@if(!$contractor)
    <div class="awt-glass-card p-5 text-center">
        <div class="awt-empty-state-icon text-muted mb-2">
            <i class="icon-base ti tabler-user-off fs-1"></i>
        </div>
        <h5 class="fw-semibold mb-1">No contractor selected</h5>
        <p class="text-muted small mb-0">Select a contractor from the dropdown above to view the ledger.</p>
    </div>
@else
    {{-- Statement Header Info --}}
    <div class="awt-glass-card p-4 mb-4">
        <div class="d-flex justify-content-between align-items-start border-bottom pb-3 mb-3">
            <div>
                <h4 class="fw-bold mb-1 text-body">{{ $contractor->name }}</h4>
                <div class="text-muted small">{{ $contractor->company_name ?? 'Individual Contractor' }} &bull; Phone: {{ $contractor->phone ?? '—' }} &bull; CNIC: {{ $contractor->cnic ?? '—' }}</div>
            </div>
            <div class="text-end">
                <span class="badge bg-label-secondary p-2">As of {{ date('d M Y') }}</span>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-md-4">
                <div class="awt-kpi-card p-3 text-center">
                    <div class="awt-kpi-title mb-1">Total Contract Value</div>
                    <div class="awt-kpi-value text-body">PKR {{ number_format($totalContract, 0) }}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="awt-kpi-card p-3 text-center">
                    <div class="awt-kpi-title mb-1">Total Payments Cleared</div>
                    <div class="awt-kpi-value text-success">PKR {{ number_format($totalPaid, 0) }}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="awt-kpi-card p-3 text-center">
                    <div class="awt-kpi-title mb-1">Outstanding Balance Due</div>
                    <div class="awt-kpi-value text-danger">PKR {{ number_format($remaining, 0) }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Detailed Ledger Table --}}
    <div class="awt-table-card awt-tone-secondary">
        <div class="p-3 border-bottom">
            <h5 class="fw-semibold mb-0">Disbursement History & Running Balance</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
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
                    <tr class="bg-body-tertiary">
                        <td colspan="4" class="fw-bold">Initial Contract Total</td>
                        <td class="text-end">—</td>
                        <td class="text-end fw-bold">PKR {{ number_format($totalContract, 2) }}</td>
                        <td class="d-print-none"></td>
                    </tr>
                    @forelse($ledgerEntries as $entry)
                        <tr>
                            <td>{{ $entry['date']->format('d M Y') }}</td>
                            <td class="fw-semibold text-body">{{ $entry['project'] }}</td>
                            <td><span class="badge bg-label-secondary">{{ $entry['type'] }}</span></td>
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
                                <a href="{{ route('contractor-payments.show', $entry['payment']) }}" class="btn btn-sm btn-outline-primary">
                                    #{{ str_pad($entry['payment']->id, 4, '0', STR_PAD_LEFT) }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-0">
                                <div class="awt-empty-state">
                                    <h6 class="fw-semibold mb-1">No payments disbursed yet</h6>
                                    <p class="text-muted small mb-0">Record a contractor payment to update this ledger.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="border-top">
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
@endif
@endsection
