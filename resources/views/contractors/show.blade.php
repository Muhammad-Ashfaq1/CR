@extends('layouts.app')

@section('title', $contractor->name . ' — Contractor Ledger — ' . config('app.name'))

@section('content')
{{-- Banner Intro --}}
<div class="pos-glass-card pos-tone-primary mb-4">
    <div class="pos-glass-intro">
        <div class="pos-glass-intro-icon">
            <i class="icon-base ti tabler-user-cog" aria-hidden="true"></i>
        </div>
        <div class="pos-glass-intro-content">
            <div class="pos-glass-intro-title">
                <h4 class="mb-1 text-heading fw-bold">{{ $contractor->name }}</h4>
                @if($contractor->is_active)
                    <span class="badge bg-label-success">Active Contractor</span>
                @else
                    <span class="badge bg-label-secondary">Inactive</span>
                @endif
            </div>
            <p class="pos-glass-intro-subtitle mb-0">
                <i class="icon-base ti tabler-briefcase me-1"></i> {{ $contractor->company_name ?? 'Individual' }} &bull;
                <i class="icon-base ti tabler-phone me-1"></i> {{ $contractor->phone ?? '—' }} &bull;
                CNIC: {{ $contractor->cnic ?? '—' }}
            </p>
        </div>
        <div class="pos-glass-intro-actions">
            <a href="{{ route('contractor-payments.create', ['contractor_id' => $contractor->id]) }}" class="btn btn-success">
                <i class="icon-base ti tabler-cash me-1"></i> Make Payment
            </a>
            <a href="{{ route('contractors.edit', $contractor) }}" class="btn btn-outline-primary">
                <i class="icon-base ti tabler-edit me-1"></i> Edit Profile
            </a>
        </div>
    </div>
</div>

{{-- Financial Balance Cards --}}
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-4">
        <div class="pos-glass-card pos-tone-primary h-100">
            <div class="pos-stat-body">
                <div class="pos-stat-head">
                    <span class="pos-stat-icon"><i class="icon-base ti tabler-briefcase" aria-hidden="true"></i></span>
                    <h6 class="pos-stat-label">Contract Commitments</h6>
                </div>
                <p class="pos-stat-value fs-4 fw-bold text-dark">PKR {{ number_format($totalContractSum, 0) }}</p>
                <div class="pos-stat-sub text-muted">Across all assigned projects</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-4">
        <div class="pos-glass-card pos-tone-success h-100">
            <div class="pos-stat-body">
                <div class="pos-stat-head">
                    <span class="pos-stat-icon"><i class="icon-base ti tabler-cash" aria-hidden="true"></i></span>
                    <h6 class="pos-stat-label">Total Cleared Disbursements</h6>
                </div>
                <p class="pos-stat-value fs-4 fw-bold text-success">PKR {{ number_format($totalPaidSum, 0) }}</p>
                <div class="pos-stat-sub text-muted">Cleared vouchers</div>
            </div>
        </div>
    </div>
    <div class="col-sm-12 col-xl-4">
        <div class="pos-glass-card pos-tone-danger h-100">
            <div class="pos-stat-body">
                <div class="pos-stat-head">
                    <span class="pos-stat-icon"><i class="icon-base ti tabler-hourglass" aria-hidden="true"></i></span>
                    <h6 class="pos-stat-label">Total Outstanding Balance</h6>
                </div>
                <p class="pos-stat-value fs-4 fw-bold text-danger">PKR {{ number_format($totalRemainingSum, 0) }}</p>
                <div class="pos-stat-sub text-muted">Pending balance</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    {{-- Project Agreements Breakdown --}}
    <div class="col-lg-7">
        <div class="pos-listing mb-4">
            <div class="pos-glass-card pos-tone-secondary pos-listing-panel">
                <div class="pos-listing-toolbar">
                    <h5 class="pos-listing-title mb-0">Project Agreements & Statement</h5>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Project Site</th>
                                <th>Contract Amount</th>
                                <th>Paid to Date</th>
                                <th>Remaining Due</th>
                                <th class="text-end">Pay</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($projectLedgers as $item)
                                <tr>
                                    <td>
                                        <a href="{{ route('projects.show', $item['project']) }}" class="fw-semibold text-heading text-decoration-none">
                                            {{ $item['project']->name }}
                                        </a>
                                        @if($item['is_primary'])
                                            <span class="badge bg-label-primary ms-1">Primary</span>
                                        @endif
                                        <div class="small text-muted">{{ $item['project']->location ?? '—' }}</div>
                                    </td>
                                    <td class="fw-semibold">PKR {{ number_format($item['contract_amount'], 0) }}</td>
                                    <td class="text-success fw-semibold">PKR {{ number_format($item['total_paid'], 0) }}</td>
                                    <td class="text-danger fw-semibold">PKR {{ number_format($item['remaining'], 0) }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('contractor-payments.create', ['project_id' => $item['project']->id, 'contractor_id' => $contractor->id]) }}" class="btn btn-sm btn-outline-success">
                                            <i class="icon-base ti tabler-cash"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">No projects currently assigned to this contractor.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Payment History Vouchers --}}
        <div class="pos-glass-card pos-tone-secondary">
            <div class="card-header">
                <h5 class="card-title mb-0">Payment Transaction Log</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Project</th>
                                <th>Type</th>
                                <th>Reference</th>
                                <th class="text-end">Amount</th>
                                <th class="text-end">Voucher</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($contractor->payments as $payment)
                                <tr class="{{ $payment->is_voided ? 'text-decoration-line-through text-muted' : '' }}">
                                    <td>{{ $payment->payment_date->format('d M Y') }}</td>
                                    <td>{{ $payment->project?->name }}</td>
                                    <td><span class="badge bg-label-info">{{ $payment->payment_type->label() }}</span></td>
                                    <td>{{ $payment->reference ?? '—' }}</td>
                                    <td class="text-end fw-semibold text-success">PKR {{ number_format($payment->amount, 0) }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('contractor-payments.show', $payment) }}" class="btn btn-xs btn-outline-secondary">
                                            #{{ str_pad($payment->id, 4, '0', STR_PAD_LEFT) }}
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-3 text-muted">No payment records found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Workforce managed by this contractor --}}
    <div class="col-lg-5">
        <div class="pos-glass-card pos-tone-info">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Contractor Workforce</h5>
                <a href="{{ route('workers.create') }}" class="btn btn-sm btn-outline-primary">+ Add Worker</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Worker</th>
                                <th>Trade</th>
                                <th>Wage/Day</th>
                                <th class="text-end">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($contractor->workers as $worker)
                                <tr>
                                    <td>
                                        <a href="{{ route('workers.show', $worker) }}" class="fw-semibold text-heading text-decoration-none">
                                            {{ $worker->name }}
                                        </a>
                                        <div class="small text-muted">{{ $worker->phone ?? '—' }}</div>
                                    </td>
                                    <td>{{ $worker->worker_type }}</td>
                                    <td class="fw-semibold">PKR {{ number_format($worker->daily_wage, 0) }}</td>
                                    <td class="text-end">
                                        <span class="badge {{ $worker->status === 'active' ? 'bg-label-success' : 'bg-label-secondary' }}">
                                            {{ ucfirst($worker->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">No workers linked to this contractor.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
