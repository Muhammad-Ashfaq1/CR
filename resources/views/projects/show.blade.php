@extends('layouts.app')

@section('title', $project->name . ' — ' . config('app.name'))

@section('content')
{{-- Intro Banner --}}
<div class="awt-glass-card awt-tone-primary mb-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="avatar avatar-lg rounded-3 bg-label-primary d-flex align-items-center justify-content-center">
                <i class="icon-base ti tabler-building-skyscraper fs-2"></i>
            </div>
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <h4 class="awt-dash-title mb-0">{{ $project->name }}</h4>
                    <span class="badge {{ $project->status->badgeClass() }}">{{ $project->status->label() }}</span>
                </div>
                <div class="awt-dash-subtitle mb-0">
                    <i class="icon-base ti tabler-map-pin me-1"></i> {{ $project->location ?? 'No location' }} &bull;
                    Site: {{ $project->site_name ?? '—' }} &bull;
                    Owner: {{ $project->owner?->name ?? '—' }}
                </div>
            </div>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <a href="{{ route('attendance.index', ['project_id' => $project->id]) }}" class="btn btn-warning text-dark fw-semibold">
                <i class="icon-base ti tabler-calendar-check me-1"></i> Attendance
            </a>
            <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#createExpenseModal">
                <i class="icon-base ti tabler-receipt me-1"></i> Add Expense
            </button>
            @if(auth()->user()->isOwner() || auth()->user()->isAdmin())
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createPaymentModal">
                    <i class="icon-base ti tabler-cash me-1"></i> Pay Contractor
                </button>
                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editProjectModal">
                    <i class="icon-base ti tabler-edit me-1"></i> Edit Project
                </button>
            @endif
        </div>
    </div>
</div>

{{-- Financial KPI Ribbon --}}
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-2">
        <div class="awt-kpi-card p-3 h-100">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="awt-kpi-title">Contract Value</span>
                <div class="avatar avatar-sm bg-label-primary rounded-circle d-flex align-items-center justify-content-center">
                    <i class="icon-base ti tabler-briefcase"></i>
                </div>
            </div>
            <div class="awt-kpi-value fs-6 fw-bold text-body mb-1">PKR {{ number_format($totalContractAmount, 0) }}</div>
            <div class="awt-kpi-footer text-muted">All commitments</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-2">
        <div class="awt-kpi-card p-3 h-100">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="awt-kpi-title">Paid to Date</span>
                <div class="avatar avatar-sm bg-label-success rounded-circle d-flex align-items-center justify-content-center">
                    <i class="icon-base ti tabler-cash"></i>
                </div>
            </div>
            <div class="awt-kpi-value fs-6 fw-bold text-success mb-1">PKR {{ number_format($totalPaidToContractors, 0) }}</div>
            <div class="awt-kpi-footer text-muted">Cleared vouchers</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-2">
        <div class="awt-kpi-card p-3 h-100">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="awt-kpi-title">Balance Due</span>
                <div class="avatar avatar-sm bg-label-danger rounded-circle d-flex align-items-center justify-content-center">
                    <i class="icon-base ti tabler-hourglass"></i>
                </div>
            </div>
            <div class="awt-kpi-value fs-6 fw-bold text-danger mb-1">PKR {{ number_format($remainingContractorBalance, 0) }}</div>
            <div class="awt-kpi-footer text-muted">Pending balance</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="awt-kpi-card p-3 h-100">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="awt-kpi-title">Direct Materials</span>
                <div class="avatar avatar-sm bg-label-warning rounded-circle d-flex align-items-center justify-content-center">
                    <i class="icon-base ti tabler-receipt"></i>
                </div>
            </div>
            <div class="awt-kpi-value fs-6 fw-bold text-warning mb-1">PKR {{ number_format($totalDirectExpenses, 0) }}</div>
            <div class="awt-kpi-footer text-muted">Bricks, cement, fuel</div>
        </div>
    </div>
    <div class="col-sm-12 col-xl-3">
        <div class="awt-kpi-card p-3 h-100">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="awt-kpi-title">Total Outlay</span>
                <div class="avatar avatar-sm bg-label-info rounded-circle d-flex align-items-center justify-content-center">
                    <i class="icon-base ti tabler-calculator"></i>
                </div>
            </div>
            <div class="awt-kpi-value fs-5 fw-bold text-primary mb-1">PKR {{ number_format($totalProjectOutlay, 0) }}</div>
            <div class="awt-kpi-footer text-muted">Paid + Direct Expenses</div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    {{-- Contractors Assigned --}}
    <div class="col-lg-7">
        <div class="awt-table-card awt-tone-secondary mb-4">
            <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
                <h5 class="fw-semibold mb-0"><i class="icon-base ti tabler-user-cog me-2 text-primary"></i> Contractor Agreements & Balance</h5>
                @if(auth()->user()->isOwner() || auth()->user()->isAdmin())
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#assignContractorModal">
                        <i class="icon-base ti tabler-plus me-1"></i> Assign Contractor
                    </button>
                @endif
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="border-bottom">
                        <tr>
                            <th class="text-uppercase small fw-semibold text-muted ps-3">Contractor</th>
                            <th class="text-uppercase small fw-semibold text-muted">Contract</th>
                            <th class="text-uppercase small fw-semibold text-muted">Paid</th>
                            <th class="text-uppercase small fw-semibold text-muted">Balance Due</th>
                            <th class="text-uppercase small fw-semibold text-muted text-end pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($project->contractors as $contractor)
                            @php
                                $cAmount = (float) $contractor->pivot->contract_amount;
                                $cPaid = $contractor->totalPaidForProject($project->id);
                                $cRemaining = max(0, $cAmount - $cPaid);
                                $percent = $cAmount > 0 ? min(100, round(($cPaid / $cAmount) * 100)) : 0;
                            @endphp
                            <tr>
                                <td class="ps-3">
                                    <a href="{{ route('contractors.show', $contractor) }}" class="fw-semibold text-primary text-decoration-none">
                                        {{ $contractor->name }}
                                    </a>
                                    @if($contractor->pivot->is_primary)
                                        <span class="badge bg-label-primary ms-1">Primary</span>
                                    @endif
                                    <div class="small text-muted">{{ $contractor->phone ?? $contractor->company_name }}</div>
                                </td>
                                <td class="fw-semibold">PKR {{ number_format($cAmount, 0) }}</td>
                                <td class="text-success fw-semibold">
                                    PKR {{ number_format($cPaid, 0) }}
                                    <div class="progress mt-1" style="height: 4px;">
                                        <div class="progress-bar bg-success" style="width: {{ $percent }}%"></div>
                                    </div>
                                </td>
                                <td class="text-danger fw-semibold">PKR {{ number_format($cRemaining, 0) }}</td>
                                <td class="text-end pe-3">
                                    <button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#payContractorModal_{{ $contractor->id }}" title="Record Payment">
                                        <i class="icon-base ti tabler-cash"></i> Pay
                                    </button>
                                    @if(auth()->user()->isAdmin() || auth()->user()->isOwner())
                                        <form id="remContrForm_{{ $contractor->id }}" action="{{ route('projects.contractors.remove', [$project, $contractor]) }}" method="POST" class="d-inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-sm btn-icon btn-text-danger rounded-pill" title="Remove" onclick="confirmDelete(() => document.getElementById('remContrForm_{{ $contractor->id }}').submit(), 'Remove contractor from this project?')">
                                                <i class="icon-base ti tabler-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-0">
                                    <div class="awt-empty-state">
                                        <h6 class="fw-semibold mb-1">No contractors assigned</h6>
                                        <p class="text-muted small mb-0">Assign a contractor to start tracking project agreements.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Direct Expenses Breakdown Card --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0"><i class="icon-base ti tabler-receipt me-2 text-primary"></i> Direct Expenses by Category</h5>
                <a href="{{ route('expenses.index', ['project_id' => $project->id]) }}" class="btn btn-sm btn-outline-primary">All Expenses</a>
            </div>
            <div class="card-body">
                <div class="row g-3 mb-3">
                    <div class="col-6 col-md-3">
                        <div class="p-3 rounded bg-light border">
                            <div class="small text-muted">Materials</div>
                            <div class="fw-bold fs-6 text-dark">PKR {{ number_format($materialExpenses, 0) }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-3 rounded bg-light border">
                            <div class="small text-muted">Labor / Wages</div>
                            <div class="fw-bold fs-6 text-dark">PKR {{ number_format($laborExpenses, 0) }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-3 rounded bg-light border">
                            <div class="small text-muted">Machinery & Rent</div>
                            <div class="fw-bold fs-6 text-dark">PKR {{ number_format($equipmentExpenses, 0) }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-3 rounded bg-light border">
                            <div class="small text-muted">Utilities & Fuel</div>
                            <div class="fw-bold fs-6 text-dark">PKR {{ number_format($utilitiesExpenses + $otherExpenses, 0) }}</div>
                        </div>
                    </div>
                </div>

                <h6 class="fw-semibold mb-2 mt-4">Recent Site Expenses</h6>
                <div class="table-responsive">
                    <table class="table align-middle table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Category</th>
                                <th>Vendor / Detail</th>
                                <th>Method</th>
                                <th class="text-end">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentExpenses as $exp)
                                <tr>
                                    <td>{{ $exp->expense_date->format('d M Y') }}</td>
                                    <td>
                                        <span class="badge" style="background-color: {{ $exp->category?->color }}20; color: {{ $exp->category?->color }};">
                                            {{ $exp->category?->name ?? 'Expense' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $exp->vendor ?? '—' }}</div>
                                        <div class="small text-muted">{{ Str::limit($exp->description, 40) }}</div>
                                    </td>
                                    <td><span class="badge bg-label-secondary">{{ $exp->payment_method ?? 'Cash' }}</span></td>
                                    <td class="text-end fw-semibold text-dark">PKR {{ number_format($exp->amount, 0) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-3 text-muted">No expenses recorded yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Right Sidebar: Workforce & Recent Payments --}}
    <div class="col-lg-5">
        {{-- Workforce summary --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0"><i class="icon-base ti tabler-hammer me-2 text-info"></i> Site Workforce</h5>
                <a href="{{ route('workers.index', ['project_id' => $project->id]) }}" class="btn btn-sm btn-outline-secondary">View Roster</a>
            </div>
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-3 p-3 bg-light rounded">
                    <div>
                        <div class="fs-4 fw-bold text-dark">{{ $project->workers->count() }}</div>
                        <div class="small text-muted">Active Workers on Site</div>
                    </div>
                    <div>
                        <a href="{{ route('attendance.index', ['project_id' => $project->id]) }}" class="btn btn-warning btn-sm text-dark fw-semibold">
                            <i class="icon-base ti tabler-calendar-check me-1"></i> Today's Sheet
                        </a>
                    </div>
                </div>

                <div class="list-group list-group-flush">
                    @forelse($project->workers->take(5) as $w)
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <div>
                                <a href="{{ route('workers.show', $w) }}" class="fw-semibold text-dark text-decoration-none">{{ $w->name }}</a>
                                <div class="small text-muted">{{ $w->worker_type }} &bull; {{ $w->contractor?->name }}</div>
                            </div>
                            <span class="badge bg-label-secondary">PKR {{ number_format($w->daily_wage, 0) }}/day</span>
                        </div>
                    @empty
                        <div class="text-muted text-center py-2">No workers registered for this project yet.</div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Contractor Payment History --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0"><i class="icon-base ti tabler-cash me-2 text-success"></i> Recent Contractor Payments</h5>
                <a href="{{ route('contractor-payments.index', ['project_id' => $project->id]) }}" class="btn btn-sm btn-outline-secondary">All Payments</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Contractor</th>
                                <th>Type</th>
                                <th class="text-end">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentPayments as $payment)
                                <tr>
                                    <td>{{ $payment->payment_date->format('d M Y') }}</td>
                                    <td>{{ $payment->contractor?->name }}</td>
                                    <td><span class="badge bg-label-info">{{ $payment->payment_type->label() }}</span></td>
                                    <td class="text-end fw-semibold text-success">PKR {{ number_format($payment->amount, 0) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-3 text-muted">No payments recorded yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Assign Contractor Modal --}}
<div class="modal fade" id="assignContractorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white"><i class="icon-base ti tabler-user-plus me-2"></i> Assign Contractor to Project</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('projects.contractors.assign', $project) }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label required fw-semibold">Select Contractor</label>
                        <select name="contractor_id" class="form-select" required>
                            <option value="">Choose Contractor...</option>
                            @foreach($availableContractors as $c)
                                <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->company_name ?? 'Individual' }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required fw-semibold">Total Agreed Contract Amount (PKR)</label>
                        <input type="number" step="0.01" name="contract_amount" class="form-control fs-5 fw-bold" placeholder="e.g. 2500000" required>
                    </div>
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_primary" value="1" id="primaryCheck" checked>
                            <label class="form-check-label fw-semibold" for="primaryCheck">Set as Primary Contractor</label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Agreement Notes / Milestones</label>
                        <textarea name="agreement_notes" class="form-control" rows="2" placeholder="Milestone schedule, deliverables..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="icon-base ti tabler-check me-1"></i> Assign Contractor</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Add Expense Modal --}}
<div class="modal fade" id="createExpenseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white"><i class="icon-base ti tabler-receipt me-2"></i> Record Direct Site Expense</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('expenses.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="project_id" value="{{ $project->id }}">
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label required fw-semibold">Expense Category</label>
                            <select name="expense_category_id" class="form-select" required>
                                <option value="">Select Category...</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }} ({{ ucfirst($cat->type) }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required fw-semibold">Amount (PKR)</label>
                            <input type="number" step="0.01" name="amount" class="form-control fs-5 fw-bold text-primary" placeholder="e.g. 85000" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required fw-semibold">Expense Date</label>
                            <input type="date" name="expense_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Vendor / Supplier / Store</label>
                            <input type="text" name="vendor" class="form-control" placeholder="e.g. Lucky Cement Agency">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Payment Method</label>
                            <select name="payment_method" class="form-select">
                                @foreach($paymentMethods as $pm)
                                    <option value="{{ $pm }}">{{ $pm }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Upload Receipt Image (Optional)</label>
                            <input type="file" name="receipt" class="form-control" accept="image/*,.pdf">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Itemized Description / Bill Details</label>
                            <textarea name="description" class="form-control" rows="2" placeholder="e.g. 100 Bags OPC Cement delivered to site"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="icon-base ti tabler-check me-1"></i> Save Expense</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Record Payment Modal --}}
<div class="modal fade" id="createPaymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title text-white"><i class="icon-base ti tabler-cash me-2"></i> Record Contractor Payment Voucher</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('contractor-payments.store') }}" method="POST">
                @csrf
                <input type="hidden" name="project_id" value="{{ $project->id }}">
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label required fw-semibold">Select Contractor</label>
                            <select name="contractor_id" class="form-select" required>
                                <option value="">Choose Contractor...</option>
                                @foreach($project->contractors as $c)
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
                            <textarea name="notes" class="form-control" rows="2" placeholder="e.g. Milestone payment for slab casting"></textarea>
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

{{-- Pay Contractor per-row Modals --}}
@foreach($project->contractors as $c)
<div class="modal fade" id="payContractorModal_{{ $c->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title text-white"><i class="icon-base ti tabler-cash me-2"></i> Pay Contractor: {{ $c->name }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('contractor-payments.store') }}" method="POST">
                @csrf
                <input type="hidden" name="project_id" value="{{ $project->id }}">
                <input type="hidden" name="contractor_id" value="{{ $c->id }}">
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
                            <textarea name="notes" class="form-control" rows="2" placeholder="e.g. Milestone installment"></textarea>
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

{{-- Edit Project Modal --}}
@if(auth()->user()->isAdmin() || auth()->user()->isOwner())
<div class="modal fade" id="editProjectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white"><i class="icon-base ti tabler-edit me-2"></i> Edit Project: {{ $project->name }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('projects.update', $project) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label required fw-semibold">Project Name</label>
                            <input type="text" name="name" class="form-control" value="{{ $project->name }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Site / Plot Identifier</label>
                            <input type="text" name="site_name" class="form-control" value="{{ $project->site_name }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Location / City</label>
                            <input type="text" name="location" class="form-control" value="{{ $project->location }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required fw-semibold">Status</label>
                            <select name="status" class="form-select" required>
                                @foreach($statuses as $status)
                                    <option value="{{ $status->value }}" {{ $project->status->value === $status->value ? 'selected' : '' }}>
                                        {{ $status->label() }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Start Date</label>
                            <input type="date" name="start_date" class="form-control" value="{{ $project->start_date?->format('Y-m-d') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Expected Completion</label>
                            <input type="date" name="expected_completion_date" class="form-control" value="{{ $project->expected_completion_date?->format('Y-m-d') }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Project Description / Notes</label>
                            <textarea name="notes" class="form-control" rows="2">{{ $project->notes }}</textarea>
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
@endif

@endsection
