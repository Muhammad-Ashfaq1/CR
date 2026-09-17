@extends('layouts.app')

@section('title', $project->name . ' — ' . config('app.name'))

@section('content')
{{-- Intro Banner --}}
<div class="pos-glass-card pos-tone-primary mb-4">
    <div class="pos-glass-intro">
        <div class="pos-glass-intro-icon">
            <i class="icon-base ti tabler-building-skyscraper" aria-hidden="true"></i>
        </div>
        <div class="pos-glass-intro-content">
            <div class="pos-glass-intro-title">
                <h4 class="mb-1 text-heading fw-bold">{{ $project->name }}</h4>
                <span class="badge {{ $project->status->badgeClass() }}">{{ $project->status->label() }}</span>
            </div>
            <p class="pos-glass-intro-subtitle mb-0">
                <i class="icon-base ti tabler-map-pin me-1"></i> {{ $project->location ?? 'No location' }} &bull;
                Site: {{ $project->site_name ?? '—' }} &bull;
                Owner: {{ $project->owner?->name ?? '—' }}
            </p>
        </div>
        <div class="pos-glass-intro-actions">
            <a href="{{ route('attendance.index', ['project_id' => $project->id]) }}" class="btn btn-warning">
                <i class="icon-base ti tabler-calendar-check me-1"></i> Attendance
            </a>
            <a href="{{ route('expenses.create', ['project_id' => $project->id]) }}" class="btn btn-label-secondary">
                <i class="icon-base ti tabler-receipt me-1"></i> Add Expense
            </a>
            @if(auth()->user()->isOwner() || auth()->user()->isAdmin())
                <a href="{{ route('contractor-payments.create', ['project_id' => $project->id]) }}" class="btn btn-success">
                    <i class="icon-base ti tabler-cash me-1"></i> Pay Contractor
                </a>
                <a href="{{ route('projects.edit', $project) }}" class="btn btn-outline-primary">
                    <i class="icon-base ti tabler-edit me-1"></i> Edit
                </a>
            @endif
        </div>
    </div>
</div>

{{-- Financial KPI Ribbon --}}
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-2">
        <div class="pos-glass-card pos-tone-primary h-100">
            <div class="pos-stat-body">
                <div class="pos-stat-head">
                    <span class="pos-stat-icon"><i class="icon-base ti tabler-briefcase" aria-hidden="true"></i></span>
                    <h6 class="pos-stat-label">Contract Value</h6>
                </div>
                <p class="pos-stat-value fs-6 fw-bold text-dark">PKR {{ number_format($totalContractAmount, 0) }}</p>
                <div class="pos-stat-sub text-muted">All commitments</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-2">
        <div class="pos-glass-card pos-tone-success h-100">
            <div class="pos-stat-body">
                <div class="pos-stat-head">
                    <span class="pos-stat-icon"><i class="icon-base ti tabler-cash" aria-hidden="true"></i></span>
                    <h6 class="pos-stat-label">Paid to Date</h6>
                </div>
                <p class="pos-stat-value fs-6 fw-bold text-success">PKR {{ number_format($totalPaidToContractors, 0) }}</p>
                <div class="pos-stat-sub text-muted">Cleared payments</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-2">
        <div class="pos-glass-card pos-tone-danger h-100">
            <div class="pos-stat-body">
                <div class="pos-stat-head">
                    <span class="pos-stat-icon"><i class="icon-base ti tabler-hourglass" aria-hidden="true"></i></span>
                    <h6 class="pos-stat-label">Balance Due</h6>
                </div>
                <p class="pos-stat-value fs-6 fw-bold text-danger">PKR {{ number_format($remainingContractorBalance, 0) }}</p>
                <div class="pos-stat-sub text-muted">Pending balance</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="pos-glass-card pos-tone-warning h-100">
            <div class="pos-stat-body">
                <div class="pos-stat-head">
                    <span class="pos-stat-icon"><i class="icon-base ti tabler-receipt" aria-hidden="true"></i></span>
                    <h6 class="pos-stat-label">Direct Materials</h6>
                </div>
                <p class="pos-stat-value fs-6 fw-bold text-warning">PKR {{ number_format($totalDirectExpenses, 0) }}</p>
                <div class="pos-stat-sub text-muted">Bricks, cement, fuel</div>
            </div>
        </div>
    </div>
    <div class="col-sm-12 col-xl-3">
        <div class="pos-glass-card pos-tone-primary h-100">
            <div class="pos-stat-body">
                <div class="pos-stat-head">
                    <span class="pos-stat-icon"><i class="icon-base ti tabler-calculator" aria-hidden="true"></i></span>
                    <h6 class="pos-stat-label">Total Outlay</h6>
                </div>
                <p class="pos-stat-value fs-5 fw-bold text-primary">PKR {{ number_format($totalProjectOutlay, 0) }}</p>
                <div class="pos-stat-sub text-muted">Paid + Direct Expenses</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    {{-- Contractors Assigned --}}
    <div class="col-lg-7">
        <div class="pos-listing mb-4">
            <div class="pos-glass-card pos-tone-secondary pos-listing-panel">
                <div class="pos-listing-toolbar d-flex justify-content-between align-items-center">
                    <h5 class="pos-listing-title mb-0">Contractor Agreements & Balance</h5>
                    @if(auth()->user()->isOwner() || auth()->user()->isAdmin())
                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#assignContractorModal">
                            <i class="icon-base ti tabler-plus me-1"></i> Assign Contractor
                        </button>
                    @endif
                </div>

                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Contractor</th>
                                <th>Contract</th>
                                <th>Paid</th>
                                <th>Balance Due</th>
                                <th class="text-end">Actions</th>
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
                                    <td>
                                        <a href="{{ route('contractors.show', $contractor) }}" class="fw-semibold text-heading text-decoration-none">
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
                                    <td class="text-end">
                                        <a href="{{ route('contractor-payments.create', ['project_id' => $project->id, 'contractor_id' => $contractor->id]) }}" class="btn btn-sm btn-outline-success" title="Record Payment">
                                            <i class="icon-base ti tabler-cash"></i> Pay
                                        </a>
                                        @if(auth()->user()->isAdmin() || auth()->user()->isOwner())
                                            <form action="{{ route('projects.contractors.remove', [$project, $contractor]) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Remove contractor from this project?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-icon btn-outline-danger" title="Remove">
                                                    <i class="icon-base ti tabler-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        No contractors assigned to this project yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Direct Expenses Breakdown Card --}}
        <div class="pos-glass-card pos-tone-secondary">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Direct Expenses by Category</h5>
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
                            <div class="small text-muted">Labor / Petty Wages</div>
                            <div class="fw-bold fs-6 text-dark">PKR {{ number_format($laborExpenses, 0) }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-3 rounded bg-light border">
                            <div class="small text-muted">Machinery & Equip</div>
                            <div class="fw-bold fs-6 text-dark">PKR {{ number_format($equipmentExpenses, 0) }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-3 rounded bg-light border">
                            <div class="small text-muted">Utilities & Admin</div>
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
                                        <div>{{ $exp->vendor ?? '—' }}</div>
                                        <div class="small text-muted">{{ Str::limit($exp->description, 40) }}</div>
                                    </td>
                                    <td>{{ $exp->payment_method ?? 'Cash' }}</td>
                                    <td class="text-end fw-semibold">PKR {{ number_format($exp->amount, 0) }}</td>
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
        <div class="pos-glass-card pos-tone-info mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Site Workforce</h5>
                <a href="{{ route('workers.index', ['project_id' => $project->id]) }}" class="btn btn-sm btn-outline-secondary">View Workers</a>
            </div>
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-3 p-3 bg-light rounded">
                    <div>
                        <div class="fs-4 fw-bold text-dark">{{ $project->workers->count() }}</div>
                        <div class="small text-muted">Registered Workers</div>
                    </div>
                    <div>
                        <a href="{{ route('attendance.index', ['project_id' => $project->id]) }}" class="btn btn-warning btn-sm">
                            <i class="icon-base ti tabler-calendar-check me-1"></i> Today's Sheet
                        </a>
                    </div>
                </div>

                <div class="list-group list-group-flush">
                    @forelse($project->workers->take(5) as $w)
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <div>
                                <div class="fw-semibold text-heading">{{ $w->name }}</div>
                                <div class="small text-muted">{{ $w->worker_type }} &bull; Contractor: {{ $w->contractor?->name }}</div>
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
        <div class="pos-glass-card pos-tone-success">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Recent Contractor Payments</h5>
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
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('projects.contractors.assign', $project) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="icon-base ti tabler-user-plus me-1"></i> Assign Contractor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label required">Select Contractor</label>
                        <select name="contractor_id" class="form-select" required>
                            <option value="">Choose Contractor...</option>
                            @foreach($availableContractors as $c)
                                <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->company_name ?? 'Individual' }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Total Agreed Contract Amount (PKR)</label>
                        <input type="number" step="0.01" name="contract_amount" class="form-control" placeholder="e.g. 2500000" required>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_primary" value="1" id="primaryCheck" checked>
                            <label class="form-check-label" for="primaryCheck">Set as Primary Contractor</label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Agreement Notes / Milestones</label>
                        <textarea name="agreement_notes" class="form-control" rows="2" placeholder="Milestone schedule, terms..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Assign Contractor</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
