@extends('layouts.app')

@section('title', $contractor->name . ' — Contractor Ledger — ' . config('app.name'))

@section('content')
{{-- Banner Intro --}}
<div class="pos-glass-intro pos-tone-primary mb-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <h4 class="pos-glass-intro-title mb-0">{{ $contractor->name }}</h4>
                @if($contractor->is_active)
                    <span class="badge bg-label-success">Active Contractor</span>
                @else
                    <span class="badge bg-label-secondary">Inactive</span>
                @endif
            </div>
            <div class="pos-glass-intro-sub">
                <i class="icon-base ti tabler-briefcase me-1"></i> {{ $contractor->company_name ?? 'Individual' }} &bull;
                <i class="icon-base ti tabler-phone me-1"></i> {{ $contractor->phone ?? '—' }} &bull;
                CNIC: {{ $contractor->cnic ?? '—' }}
            </div>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            @if(auth()->user()->isAdmin() || session()->has('impersonator_id'))
                <form id="impContrShowForm" action="{{ route('impersonate.contractor', $contractor) }}" method="POST" class="d-inline-block">
                    @csrf
                    <button type="button" class="btn btn-warning text-dark fw-semibold" onclick="confirmImpersonate('{{ addslashes($contractor->name) }}', 'Contractor', () => document.getElementById('impContrShowForm').submit())">
                        <i class="icon-base ti tabler-user-check me-1"></i> Impersonate
                    </button>
                </form>
            @endif
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createPaymentModal">
                <i class="icon-base ti tabler-cash me-1"></i> Make Payment
            </button>
            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editContractorModal">
                <i class="icon-base ti tabler-edit me-1"></i> Edit Profile
            </button>
            <a href="{{ route('contractors.index') }}" class="btn btn-outline-secondary">
                <i class="icon-base ti tabler-arrow-left me-1"></i> Directory
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
        <div class="card awt-table-card awt-tone-secondary mb-4">
            <div class="awt-listing-toolbar">
                <h5 class="mb-0 fw-semibold"><i class="icon-base ti tabler-building-skyscraper me-2 text-primary"></i> Project Agreements & Statement</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
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
                                    <a href="{{ route('projects.show', $item['project']) }}" class="fw-semibold text-primary text-decoration-none">
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
                                    <button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#payProjModal_{{ $item['project']->id }}">
                                        <i class="icon-base ti tabler-cash"></i> Pay
                                    </button>
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

        {{-- Payment History Vouchers --}}
        <div class="card awt-table-card awt-tone-secondary mb-4">
            <div class="awt-listing-toolbar">
                <h5 class="mb-0 fw-semibold"><i class="icon-base ti tabler-history me-2 text-primary"></i> Payment Transaction Log</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
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

    {{-- Workforce managed by this contractor --}}
    <div class="col-lg-5">
        <div class="card awt-table-card awt-tone-secondary mb-4">
            <div class="awt-listing-toolbar d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-semibold"><i class="icon-base ti tabler-hammer me-2 text-info"></i> Contractor Workforce</h5>
                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#createWorkerModal">
                    <i class="icon-base ti tabler-plus me-1"></i> Add Worker
                </button>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
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
                                        <a href="{{ route('workers.show', $worker) }}" class="fw-semibold text-dark text-decoration-none">
                                            {{ $worker->name }}
                                        </a>
                                        <div class="small text-muted">{{ $worker->phone ?? '—' }}</div>
                                    </td>
                                    <td>{{ $worker->worker_type }}</td>
                                    <td class="fw-semibold text-dark">PKR {{ number_format($worker->daily_wage, 0) }}</td>
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

{{-- Global Payment Modal --}}
<div class="modal fade" id="createPaymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title text-white"><i class="icon-base ti tabler-cash me-2"></i> Record Payment to {{ $contractor->name }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('contractor-payments.store') }}" method="POST">
                @csrf
                <input type="hidden" name="contractor_id" value="{{ $contractor->id }}">
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label required fw-semibold">Select Project</label>
                            <select name="project_id" class="form-select" required>
                                <option value="">Choose Project...</option>
                                @foreach($contractor->projects as $p)
                                    <option value="{{ $p->id }}">{{ $p->name }}</option>
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
                                    <option value="{{ $type->value }}">{{ $type->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Reference / Cheque / Tx ID</label>
                            <input type="text" name="reference" class="form-control" placeholder="e.g. Cheque #49281">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Notes / Description</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="e.g. Milestone payment"></textarea>
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

{{-- Per-Project Payment Modals --}}
@foreach($projectLedgers as $item)
<div class="modal fade" id="payProjModal_{{ $item['project']->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title text-white"><i class="icon-base ti tabler-cash me-2"></i> Pay {{ $contractor->name }} on {{ $item['project']->name }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('contractor-payments.store') }}" method="POST">
                @csrf
                <input type="hidden" name="contractor_id" value="{{ $contractor->id }}">
                <input type="hidden" name="project_id" value="{{ $item['project']->id }}">
                <div class="modal-body p-4">
                    <div class="row g-3">
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
                                    <option value="{{ $type->value }}">{{ $type->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Reference / Cheque / Tx ID</label>
                            <input type="text" name="reference" class="form-control" placeholder="e.g. Cheque #49281">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Notes / Description</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="e.g. Milestone payment"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success"><i class="icon-base ti tabler-check me-1"></i> Disburse Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

{{-- Edit Contractor Modal --}}
<div class="modal fade" id="editContractorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white"><i class="icon-base ti tabler-edit me-2"></i> Edit Contractor: {{ $contractor->name }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('contractors.update', $contractor) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label required fw-semibold">Contractor Name</label>
                            <input type="text" name="name" class="form-control" value="{{ $contractor->name }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Company / Firm Name</label>
                            <input type="text" name="company_name" class="form-control" value="{{ $contractor->company_name }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Phone Number</label>
                            <input type="text" name="phone" class="form-control" value="{{ $contractor->phone }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">CNIC Number</label>
                            <input type="text" name="cnic" class="form-control" value="{{ $contractor->cnic }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Address / Office Location</label>
                            <textarea name="address" class="form-control" rows="2">{{ $contractor->address }}</textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Specialization / Notes</label>
                            <textarea name="notes" class="form-control" rows="2">{{ $contractor->notes }}</textarea>
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input type="hidden" name="is_active" value="0">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="isActEdProfile" {{ $contractor->is_active ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="isActEdProfile">Contractor Active</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="icon-base ti tabler-device-floppy me-1"></i> Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Add Worker Modal --}}
<div class="modal fade" id="createWorkerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white"><i class="icon-base ti tabler-user-plus me-2"></i> Register Worker under {{ $contractor->name }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('workers.store') }}" method="POST">
                @csrf
                <input type="hidden" name="contractor_id" value="{{ $contractor->id }}">
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label required fw-semibold">Worker Full Name</label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Muhammad Rasheed" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Default Assigned Project</label>
                            <select name="project_id" class="form-select">
                                <option value="">Any Assigned Project</option>
                                @foreach($contractor->projects as $p)
                                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Phone Number</label>
                            <input type="text" name="phone" class="form-control" placeholder="03xx-xxxxxxx">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required fw-semibold">Trade / Skill Category</label>
                            <select name="worker_type" class="form-select" required>
                                @foreach($workerTypes as $type)
                                    <option value="{{ $type }}" {{ $type === 'Laborer' ? 'selected' : '' }}>{{ $type }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required fw-semibold">Initial Daily Wage (PKR)</label>
                            <input type="number" step="0.01" name="daily_wage" class="form-control fs-5 fw-bold" value="1500" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Joining Date</label>
                            <input type="date" name="joining_date" class="form-control" value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required fw-semibold">Status</label>
                            <select name="status" class="form-select" required>
                                <option value="active" selected>Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="icon-base ti tabler-check me-1"></i> Register Worker</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
