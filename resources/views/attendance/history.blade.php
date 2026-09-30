@extends('layouts.app')

@section('title', 'Attendance History & Wage Settlement — ' . config('app.name'))

@section('content')
@php
    $canManagePayments = auth()->user()->isOwner() || auth()->user()->isAdmin();
@endphp
<div class="awt-glass-card awt-tone-primary mb-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="avatar avatar-lg rounded-3 bg-label-primary d-flex align-items-center justify-content-center">
                <i class="icon-base ti tabler-history fs-2"></i>
            </div>
            <div>
                <h4 class="awt-dash-title mb-1">Attendance History & Wage Settlement</h4>
                <p class="awt-dash-subtitle mb-0">Filter shifts by custom date range, audit workforce wage collections, and execute batch payouts.</p>
            </div>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            @if((auth()->user()->isOwner() || auth()->user()->isAdmin()) && $unpaidWages > 0)
                <button type="button" class="btn btn-success fw-bold shadow-sm d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#payAllWagesModal">
                    <i class="icon-base ti tabler-cash fs-5"></i>
                    <span>Pay All Wages (PKR {{ number_format($unpaidWages, 0) }})</span>
                </button>
            @endif
            <a href="{{ route('attendance.index') }}" class="btn btn-outline-primary fw-semibold">
                <i class="icon-base ti tabler-calendar-plus me-1"></i> Today's Grid Sheet
            </a>
        </div>
    </div>
</div>

{{-- Filter & Presets Panel --}}
<div class="card awt-table-card awt-tone-secondary mb-4">
    <div class="p-3 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-2 bg-light bg-opacity-25">
        <div class="d-flex flex-wrap align-items-center gap-2">
            <span class="small fw-bold text-uppercase text-muted me-1"><i class="icon-base ti tabler-calendar-week me-1"></i> Quick Ranges:</span>
            <a href="{{ route('attendance.history', array_merge(request()->except(['preset', 'from_date', 'to_date', 'page']), ['preset' => 'this_work_week'])) }}"
               class="btn btn-sm {{ request('preset') === 'this_work_week' ? 'btn-primary' : 'btn-outline-secondary' }} rounded-pill px-3">
                This Week (Sat – Thu)
            </a>
            <a href="{{ route('attendance.history', array_merge(request()->except(['preset', 'from_date', 'to_date', 'page']), ['preset' => 'last_work_week'])) }}"
               class="btn btn-sm {{ request('preset') === 'last_work_week' ? 'btn-primary' : 'btn-outline-secondary' }} rounded-pill px-3">
                Last Week (Sat – Thu)
            </a>
            <a href="{{ route('attendance.history', array_merge(request()->except(['preset', 'from_date', 'to_date', 'page']), ['preset' => 'this_month'])) }}"
               class="btn btn-sm {{ request('preset') === 'this_month' ? 'btn-primary' : 'btn-outline-secondary' }} rounded-pill px-3">
                This Month
            </a>
        </div>
        @if(request()->hasAny(['preset', 'from_date', 'to_date', 'project_id', 'worker_id', 'contractor_id', 'status', 'payment_status']))
            <a href="{{ route('attendance.history') }}" class="btn btn-sm btn-link text-danger text-decoration-none">
                <i class="icon-base ti tabler-x me-1"></i> Clear Filters
            </a>
        @endif
    </div>

    {{-- Filter Strip --}}
    <div class="awt-listing-filter-strip">
        <form method="GET" action="{{ route('attendance.history') }}" class="row g-3 align-items-end">
            @if(request('preset') && !request('from_date'))
                <input type="hidden" name="preset" value="{{ request('preset') }}">
            @endif

            <div class="col-md-3">
                <label class="form-label small fw-semibold text-muted text-uppercase">Project</label>
                <select name="project_id" class="form-select">
                    <option value="">All Projects</option>
                    @foreach($projects as $p)
                        <option value="{{ $p->id }}" {{ request('project_id') == $p->id ? 'selected' : '' }}>
                            {{ $p->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label small fw-semibold text-muted text-uppercase">Contractor</label>
                <select name="contractor_id" class="form-select">
                    <option value="">All Contractors</option>
                    @foreach($contractors as $c)
                        <option value="{{ $c->id }}" {{ request('contractor_id') == $c->id ? 'selected' : '' }}>
                            {{ $c->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label small fw-semibold text-muted text-uppercase">Worker</label>
                <select name="worker_id" class="form-select">
                    <option value="">All Workers</option>
                    @foreach($workers as $w)
                        <option value="{{ $w->id }}" {{ request('worker_id') == $w->id ? 'selected' : '' }}>
                            {{ $w->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label small fw-semibold text-muted text-uppercase">Shift Status</label>
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    @foreach($statuses as $st)
                        <option value="{{ $st->value }}" {{ request('status') === $st->value ? 'selected' : '' }}>
                            {{ $st->label() }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label small fw-semibold text-muted text-uppercase">Payment State</label>
                <select name="payment_status" class="form-select">
                    <option value="all" {{ request('payment_status', 'all') === 'all' ? 'selected' : '' }}>All Shifts (Paid & Unpaid)</option>
                    <option value="unpaid" {{ request('payment_status') === 'unpaid' ? 'selected' : '' }}>Unpaid Wages Only</option>
                    <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>Paid Wages Only</option>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label small fw-semibold text-muted text-uppercase">From Date</label>
                <input type="date" name="from_date" class="form-control" value="{{ $fromDate }}">
            </div>

            <div class="col-md-3">
                <label class="form-label small fw-semibold text-muted text-uppercase">To Date</label>
                <input type="date" name="to_date" class="form-control" value="{{ $toDate }}">
            </div>

            <div class="col-md-6 d-flex gap-2">
                <button type="submit" class="btn btn-primary px-4 flex-grow-1">
                    <i class="icon-base ti tabler-filter me-1"></i> Apply Filters
                </button>
                <a href="{{ route('attendance.history') }}" class="btn btn-outline-secondary px-3">Reset</a>
            </div>
        </form>
    </div>
</div>

{{-- Summary Glass KPIs --}}
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="awt-kpi-card p-3">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="awt-kpi-title">Total Wages Earned</span>
                <div class="avatar avatar-sm bg-label-primary rounded-circle d-flex align-items-center justify-content-center">
                    <i class="icon-base ti tabler-wallet"></i>
                </div>
            </div>
            <div class="awt-kpi-value text-primary mb-1">PKR {{ number_format($totalWages, 0) }}</div>
            <div class="awt-kpi-footer text-muted">{{ $workerCollection->count() }} Workers Involved</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="awt-kpi-card p-3">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="awt-kpi-title">Unpaid Due Balance</span>
                <div class="avatar avatar-sm bg-label-warning rounded-circle d-flex align-items-center justify-content-center">
                    <i class="icon-base ti tabler-alert-circle"></i>
                </div>
            </div>
            <div class="awt-kpi-value text-warning mb-1">PKR {{ number_format($unpaidWages, 0) }}</div>
            <div class="awt-kpi-footer text-muted">{{ $unpaidRecordsCount }} Unsettled Shifts</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="awt-kpi-card p-3">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="awt-kpi-title">Settled / Paid Wages</span>
                <div class="avatar avatar-sm bg-label-success rounded-circle d-flex align-items-center justify-content-center">
                    <i class="icon-base ti tabler-circle-check"></i>
                </div>
            </div>
            <div class="awt-kpi-value text-success mb-1">PKR {{ number_format($paidWages, 0) }}</div>
            <div class="awt-kpi-footer text-muted">Cleared Disbursements</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="awt-kpi-card p-3">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="awt-kpi-title">Shift Breakdown</span>
                <div class="avatar avatar-sm bg-label-info rounded-circle d-flex align-items-center justify-content-center">
                    <i class="icon-base ti tabler-clock"></i>
                </div>
            </div>
            <div class="d-flex align-items-baseline gap-2 mb-1">
                <span class="awt-kpi-value text-heading mb-0">{{ $fullDaysCount }}</span>
                <small class="text-muted">Full / {{ $halfDaysCount }} Half</small>
            </div>
            <div class="awt-kpi-footer text-muted">{{ $absentCount }} Absences Recorded</div>
        </div>
    </div>
</div>

{{-- Navigation Tabs for Collection Summary vs Individual Shifts --}}
<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
    <ul class="nav nav-pills p-1 rounded-pill border d-inline-flex mb-0" role="tablist" style="background: rgba(var(--bs-primary-rgb), 0.05) !important;">
        <li class="nav-item" role="presentation">
            <button class="nav-link active rounded-pill px-4 py-2 fw-semibold d-flex align-items-center gap-2" id="collection-tab" data-bs-toggle="tab" data-bs-target="#collectionPane" type="button" role="tab">
                <i class="icon-base ti tabler-users-group"></i>
                <span>Worker Wage Collection ({{ $workerCollection->count() }})</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-pill px-4 py-2 fw-semibold d-flex align-items-center gap-2" id="shifts-tab" data-bs-toggle="tab" data-bs-target="#shiftsPane" type="button" role="tab">
                <i class="icon-base ti tabler-list-details"></i>
                <span>Detailed Shift Log ({{ $records->total() }})</span>
            </button>
        </li>
    </ul>
</div>

<div class="tab-content p-0 border-0">
    {{-- TAB 1: WORKER WAGE COLLECTION --}}
    <div class="tab-pane fade show active" id="collectionPane" role="tabpanel">
        <div class="card awt-table-card awt-tone-secondary">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Worker &amp; Trade</th>
                            <th>Contractor</th>
                            <th class="text-center">Full Days</th>
                            <th class="text-center">Half Days</th>
                            <th class="text-center">Absent</th>
                            <th class="text-center">Total Shifts</th>
                            <th class="text-end">Total Earned</th>
                            <th class="text-end">Unpaid (Due)</th>
                            <th class="text-center">Payout Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($workerCollection as $item)
                            @php
                                $worker = $item->worker;
                                $isFullyPaid = (float) $item->unpaid_amount <= 0.01;
                                $isPartiallyPaid = (float) $item->paid_amount > 0 && ! $isFullyPaid;
                            @endphp
                            <tr>
                                <td>
                                    <a href="{{ $worker ? route('workers.show', $worker) : 'javascript:void(0)' }}" class="fw-semibold text-primary text-decoration-none d-block">
                                        {{ $worker?->name ?? 'Unknown Worker' }}
                                    </a>
                                    <small class="text-muted">{{ $worker?->worker_type ?? 'Laborer' }} &bull; {{ $worker?->phone ?? 'No phone' }}</small>
                                </td>
                                <td>
                                    @if($worker?->contractor)
                                        <span class="badge bg-label-info">{{ $worker->contractor->name }}</span>
                                    @else
                                        <span class="badge bg-label-secondary">Direct Worker</span>
                                    @endif
                                </td>
                                <td class="text-center fw-medium">{{ $item->full_days }}</td>
                                <td class="text-center fw-medium">{{ $item->half_days }}</td>
                                <td class="text-center text-muted">{{ $item->absent_days }}</td>
                                <td class="text-center fw-bold">{{ $item->total_shifts }}</td>
                                <td class="text-end fw-bold text-heading">
                                    PKR {{ number_format($item->total_payable, 2) }}
                                </td>
                                <td class="text-end fw-bold {{ (float) $item->unpaid_amount > 0 ? 'text-danger' : 'text-muted' }}">
                                    PKR {{ number_format($item->unpaid_amount, 2) }}
                                </td>
                                <td class="text-center">
                                    @if($isFullyPaid)
                                        <span class="badge bg-label-success d-inline-flex align-items-center gap-1">
                                            <i class="icon-base ti tabler-check fs-6"></i> Paid in Full
                                        </span>
                                    @elseif($isPartiallyPaid)
                                        <span class="badge bg-label-warning d-inline-flex align-items-center gap-1">
                                            <i class="icon-base ti tabler-clock-pause fs-6"></i> Partially Paid
                                        </span>
                                    @else
                                        <span class="badge bg-label-danger d-inline-flex align-items-center gap-1">
                                            <i class="icon-base ti tabler-clock-x fs-6"></i> Unpaid
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="p-0">
                                    <div class="awt-empty-state text-center py-5">
                                        <div class="awt-empty-state-icon text-muted mb-2">
                                            <i class="icon-base ti tabler-users fs-1"></i>
                                        </div>
                                        <h6 class="fw-semibold mb-1">No workforce wage data found</h6>
                                        <p class="text-muted small mb-0">Select a date range (such as Saturday to Thursday) to aggregate wages.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- TAB 2: DETAILED SHIFTS LOG --}}
    <div class="tab-pane fade" id="shiftsPane" role="tabpanel">
        <div class="card awt-table-card awt-tone-secondary">
            @if($canManagePayments)
                {{-- Bulk Action Bar (Visible when 1+ rows selected) --}}
                <div id="shiftsBulkActionBar" class="card-header bg-light d-none align-items-center justify-content-between flex-wrap gap-2 py-2 px-3 border-bottom">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <span class="badge bg-primary rounded-pill px-2.5 py-1.5" id="selectedShiftsCountBadge">0</span>
                        <span class="fw-semibold text-heading small" id="selectedShiftsCountText">records selected</span>
                        <div class="vr mx-1 d-none d-sm-inline-block" style="height: 18px;"></div>
                        <button type="button" class="btn btn-xs btn-label-secondary" id="btnDeselectAllShifts">
                            <i class="icon-base ti tabler-square-x me-1"></i> Deselect All
                        </button>
                        <button type="button" class="btn btn-xs btn-label-primary" id="btnSelectAllShiftsAction">
                            <i class="icon-base ti tabler-checkbox me-1"></i> Select Page ({{ $records->count() }})
                        </button>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="small text-muted d-none d-md-inline">Bulk Payment Status:</span>
                        <button type="button" class="btn btn-sm btn-success d-flex align-items-center gap-1 shadow-sm" id="btnBulkMarkPaid">
                            <i class="icon-base ti tabler-circle-check"></i>
                            <span>Mark Paid</span>
                        </button>
                        <button type="button" class="btn btn-sm btn-label-warning d-flex align-items-center gap-1 shadow-sm" id="btnBulkMarkUnpaid">
                            <i class="icon-base ti tabler-clock-pause"></i>
                            <span>Mark Unpaid</span>
                        </button>
                    </div>
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            @if($canManagePayments)
                                <th style="width: 44px;" class="text-center px-2">
                                    <div class="form-check d-flex justify-content-center m-0">
                                        <input type="checkbox" class="form-check-input" id="selectAllShifts" title="Select or Deselect all visible rows">
                                    </div>
                                </th>
                            @endif
                            <th>Date</th>
                            <th>Worker</th>
                            <th>Project</th>
                            <th>Status</th>
                            <th>Wage at Time</th>
                            <th>Calculated Payable</th>
                            <th>Payment Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($records as $rec)
                            <tr id="shiftRow_{{ $rec->id }}">
                                @if($canManagePayments)
                                    <td class="text-center px-2">
                                        <div class="form-check d-flex justify-content-center m-0">
                                            <input type="checkbox" 
                                                   class="form-check-input shift-select-checkbox" 
                                                   value="{{ $rec->id }}" 
                                                   data-is-paid="{{ $rec->is_paid ? '1' : '0' }}"
                                                   data-worker="{{ $rec->worker?->name }}"
                                                   data-date="{{ $rec->attendance_date->format('d M Y') }}">
                                        </div>
                                    </td>
                                @endif
                                <td class="fw-medium">{{ $rec->attendance_date->format('d M Y') }}</td>
                                <td>
                                    <a href="{{ $rec->worker ? route('workers.show', $rec->worker) : 'javascript:void(0)' }}" class="fw-semibold text-primary text-decoration-none d-block">
                                        {{ $rec->worker?->name }}
                                    </a>
                                    <small class="text-muted">{{ $rec->worker?->worker_type }} &bull; {{ $rec->worker?->contractor?->name ?? 'Direct' }}</small>
                                </td>
                                <td>{{ $rec->project?->name }}</td>
                                <td>
                                    <span class="badge {{ $rec->status->badgeClass() }}">
                                        {{ $rec->status->label() }}
                                    </span>
                                </td>
                                <td>PKR {{ number_format($rec->wage_at_time, 0) }}</td>
                                <td class="fw-bold text-success">
                                    PKR {{ number_format($rec->payable_amount, 2) }}
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        @if($rec->is_paid)
                                            <span class="badge bg-label-success d-inline-flex align-items-center gap-1" title="Paid on {{ $rec->paid_at?->format('d M Y') }}">
                                                <i class="icon-base ti tabler-check fs-6"></i> Paid
                                                @if($rec->payment_reference)
                                                    <small class="text-muted ms-1">({{ $rec->payment_reference }})</small>
                                                @endif
                                            </span>
                                            @if($canManagePayments)
                                                <button type="button" 
                                                        class="btn btn-xs btn-label-warning d-inline-flex align-items-center gap-1" 
                                                        title="Directly mark this shift as Unpaid" 
                                                        onclick="confirmDirectStatusUpdate('{{ $rec->id }}', 'unpaid', '{{ addslashes($rec->worker?->name ?? 'Worker') }}', '{{ $rec->attendance_date->format('d M Y') }}')">
                                                    <i class="icon-base ti tabler-clock-pause"></i> Mark Unpaid
                                                </button>
                                            @endif
                                        @else
                                            <span class="badge bg-label-warning d-inline-flex align-items-center gap-1">
                                                <i class="icon-base ti tabler-clock fs-6"></i> Unpaid
                                            </span>
                                            @if($canManagePayments)
                                                <button type="button" 
                                                        class="btn btn-xs btn-label-success d-inline-flex align-items-center gap-1" 
                                                        title="Directly mark this shift as Paid" 
                                                        onclick="confirmDirectStatusUpdate('{{ $rec->id }}', 'paid', '{{ addslashes($rec->worker?->name ?? 'Worker') }}', '{{ $rec->attendance_date->format('d M Y') }}')">
                                                    <i class="icon-base ti tabler-check"></i> Mark Paid
                                                </button>
                                            @endif
                                        @endif
                                    </div>
                                </td>
                                <td class="text-end text-nowrap">
                                    @if($canManagePayments)
                                        @if($rec->is_paid)
                                            <button type="button" 
                                                    class="btn btn-sm btn-icon btn-text-warning rounded-pill" 
                                                    title="Mark Unpaid"
                                                    onclick="confirmDirectStatusUpdate('{{ $rec->id }}', 'unpaid', '{{ addslashes($rec->worker?->name ?? 'Worker') }}', '{{ $rec->attendance_date->format('d M Y') }}')">
                                                <i class="icon-base ti tabler-clock-pause"></i>
                                            </button>
                                        @else
                                            <button type="button" 
                                                    class="btn btn-sm btn-icon btn-text-success rounded-pill" 
                                                    title="Mark Paid"
                                                    onclick="confirmDirectStatusUpdate('{{ $rec->id }}', 'paid', '{{ addslashes($rec->worker?->name ?? 'Worker') }}', '{{ $rec->attendance_date->format('d M Y') }}')">
                                                <i class="icon-base ti tabler-circle-check"></i>
                                            </button>
                                        @endif
                                    @endif
                                    <form id="delAttForm_{{ $rec->id }}" action="{{ route('attendance.destroy', $rec) }}" method="POST" class="d-inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-sm btn-icon btn-text-danger rounded-pill" title="Delete Attendance" onclick="confirmDelete(() => document.getElementById('delAttForm_{{ $rec->id }}').submit(), 'Delete this attendance record?')">
                                            <i class="icon-base ti tabler-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $canManagePayments ? 9 : 8 }}" class="p-0">
                                    <div class="awt-empty-state text-center py-5">
                                        <div class="awt-empty-state-icon text-muted mb-2">
                                            <i class="icon-base ti tabler-calendar-off fs-1"></i>
                                        </div>
                                        <h6 class="fw-semibold mb-1">No attendance records match your filter</h6>
                                        <p class="text-muted small mb-0">Try widening your date range or adjusting the filter criteria.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($canManagePayments)
                {{-- Hidden Forms for Direct Single and Bulk Actions --}}
                <form id="directStatusToggleForm" method="POST" class="d-none">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" id="directStatusInput" value="">
                </form>

                <form id="bulkPaymentStatusForm" action="{{ route('attendance.bulk-payment-status') }}" method="POST" class="d-none">
                    @csrf
                    <input type="hidden" name="status" id="bulkStatusInput" value="">
                    <div id="bulkAttendanceIdsContainer"></div>
                </form>
            @endif

            @if($records->hasPages())
                <div class="card-footer">
                    {{ $records->fragment('shiftsPane')->links() }}
                </div>
            @endif
        </div>
    </div>
</div>


{{-- MODAL: PAY ALL WAGES --}}
@if((auth()->user()->isOwner() || auth()->user()->isAdmin()) && $unpaidWages > 0)
<div class="modal fade" id="payAllWagesModal" tabindex="-1" aria-labelledby="payAllWagesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 1rem; overflow: hidden;">
            <div class="modal-header bg-primary text-white py-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="icon-base ti tabler-cash fs-3"></i>
                    <div>
                        <h5 class="modal-title fw-bold text-white mb-0" id="payAllWagesModalLabel">Disburse Workforce Wages Payout</h5>
                        <small class="text-white text-opacity-75">Settle unpaid attendance records for the selected period</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="{{ route('attendance.pay-all') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    {{-- Summary Ribbon inside Modal --}}
                    <div class="alert alert-primary d-flex align-items-center justify-content-between p-3 mb-4 rounded-3 border-0">
                        <div>
                            <div class="text-uppercase small fw-bold text-primary opacity-75">Unpaid Amount to Disburse</div>
                            <h3 class="fw-bold text-primary mb-0">PKR {{ number_format($unpaidWages, 2) }}</h3>
                            <small class="text-muted">
                                {{ $unpaidRecordsCount }} unpaid shifts across {{ $workerCollection->where('unpaid_amount', '>', 0)->count() }} workers
                            </small>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-primary text-uppercase px-3 py-2">
                                {{ $fromDate ? \Carbon\Carbon::parse($fromDate)->format('d M') : 'Start' }} &mdash; {{ $toDate ? \Carbon\Carbon::parse($toDate)->format('d M Y') : 'End' }}
                            </span>
                        </div>
                    </div>

                    <div class="row g-3">
                        {{-- Project Selection --}}
                        <div class="col-12">
                            <label class="form-label fw-semibold">Target Construction Project <span class="text-danger">*</span></label>
                            <select name="project_id" class="form-select" required>
                                @foreach($projects as $p)
                                    <option value="{{ $p->id }}" {{ request('project_id') == $p->id ? 'selected' : '' }}>
                                        {{ $p->name }} ({{ $p->location }})
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">All unpaid attendance records under this project in the given date range will be processed.</div>
                        </div>

                        {{-- Date range preservation --}}
                        <input type="hidden" name="from_date" value="{{ $fromDate }}">
                        <input type="hidden" name="to_date" value="{{ $toDate }}">
                        <input type="hidden" name="worker_id" value="{{ request('worker_id') }}">
                        <input type="hidden" name="contractor_id" value="{{ request('contractor_id') }}">

                        {{-- Payment Destination Choice --}}
                        <div class="col-12">
                            <label class="form-label fw-bold text-uppercase small text-muted">Payment Destination & Accounting Option <span class="text-danger">*</span></label>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="card h-100 p-3 border cursor-pointer hover-shadow" style="border-radius: 0.75rem;">
                                        <div class="d-flex align-items-start gap-2">
                                            <input type="radio" name="payment_destination" value="direct_pay" class="form-check-input mt-1" checked>
                                            <div>
                                                <div class="fw-bold text-heading mb-1">
                                                    <i class="icon-base ti tabler-users text-success me-1"></i> Direct Pay
                                                </div>
                                                <small class="text-muted d-block">
                                                    Disburses directly to workers. Automatically creates a <strong>Labor Expense</strong> entry under the project.
                                                </small>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                                <div class="col-md-6">
                                    <label class="card h-100 p-3 border cursor-pointer hover-shadow" style="border-radius: 0.75rem;">
                                        <div class="d-flex align-items-start gap-2">
                                            <input type="radio" name="payment_destination" value="contractor_pay" class="form-check-input mt-1">
                                            <div>
                                                <div class="fw-bold text-heading mb-1">
                                                    <i class="icon-base ti tabler-building text-info me-1"></i> Pay to Contractor(s)
                                                </div>
                                                <small class="text-muted d-block">
                                                    Generates official <strong>Contractor Payment Voucher(s)</strong> for workers assigned to contractors.
                                                </small>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        {{-- Payment Method --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Payment Mode <span class="text-danger">*</span></label>
                            <select name="payment_method" class="form-select" required>
                                <option value="cash" selected>Cash in Hand</option>
                                <option value="bank_transfer">Bank Transfer / Online</option>
                                <option value="cheque">Cheque</option>
                                <option value="online">Mobile Wallet / Easypaisa / JazzCash</option>
                            </select>
                        </div>

                        {{-- Payment Date --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Disbursement Date <span class="text-danger">*</span></label>
                            <input type="date" name="payment_date" class="form-control" value="{{ now()->toDateString() }}" required>
                        </div>

                        {{-- Reference / Cheque --}}
                        <div class="col-12">
                            <label class="form-label fw-semibold">Reference / Tracking # (Optional)</label>
                            <input type="text" name="reference" class="form-control" placeholder="e.g. CHQ-88921 or WAGES-SAT-THU">
                        </div>

                        {{-- Notes --}}
                        <div class="col-12">
                            <label class="form-label fw-semibold">Settlement Remarks / Notes</label>
                            <textarea name="notes" rows="2" class="form-control" placeholder="Optional audit memo or worker shift comments..."></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light p-3">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success fw-bold px-4 shadow-sm">
                        <i class="icon-base ti tabler-check me-1"></i> Confirm & Disburse Wages
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Tab Persistence (remember active tab across reloads)
    var shiftsTabBtn = document.getElementById('shifts-tab');
    var collectionTabBtn = document.getElementById('collection-tab');

    function switchTab(tabId) {
        if (tabId === 'shiftsPane' && shiftsTabBtn && window.bootstrap && window.bootstrap.Tab) {
            bootstrap.Tab.getOrCreateInstance(shiftsTabBtn).show();
        } else if (tabId === 'collectionPane' && collectionTabBtn && window.bootstrap && window.bootstrap.Tab) {
            bootstrap.Tab.getOrCreateInstance(collectionTabBtn).show();
        }
    }

    var serverActiveTab = @json(session('active_tab'));
    var urlParams = new URLSearchParams(window.location.search);
    var urlTab = urlParams.get('tab');
    var hash = window.location.hash ? window.location.hash.replace('#', '') : null;
    var savedTab = sessionStorage.getItem('attendance_active_tab');

    if (serverActiveTab) {
        switchTab(serverActiveTab);
        sessionStorage.setItem('attendance_active_tab', serverActiveTab);
    } else if (hash === 'shiftsPane' || urlTab === 'shifts' || urlTab === 'shiftsPane') {
        switchTab('shiftsPane');
    } else if (savedTab) {
        switchTab(savedTab);
    }

    if (shiftsTabBtn) {
        shiftsTabBtn.addEventListener('shown.bs.tab', function () {
            sessionStorage.setItem('attendance_active_tab', 'shiftsPane');
            if (history.replaceState) {
                history.replaceState(null, null, '#shiftsPane');
            }
        });
    }
    if (collectionTabBtn) {
        collectionTabBtn.addEventListener('shown.bs.tab', function () {
            sessionStorage.setItem('attendance_active_tab', 'collectionPane');
            if (history.replaceState) {
                history.replaceState(null, null, '#collectionPane');
            }
        });
    }

    // Generic confirmation helper using AwtConfirm -> Swal -> native confirm
    function askConfirmation(options) {
        var title = options.title || 'Confirm Action';
        var message = options.message || 'Are you sure you want to proceed?';
        var confirmText = options.confirmText || 'Yes, Proceed';
        var tone = options.tone || 'primary';

        if (window.AwtConfirm && typeof window.AwtConfirm.open === 'function') {
            return window.AwtConfirm.open({
                title: title,
                message: message,
                confirmText: confirmText,
                tone: tone
            });
        }

        if (typeof Swal !== 'undefined') {
            return Swal.fire({
                title: title,
                text: message,
                icon: tone === 'danger' || tone === 'warning' ? 'warning' : 'question',
                showCancelButton: true,
                confirmButtonColor: tone === 'danger' ? '#ea5455' : (tone === 'warning' ? '#ff9f43' : '#7367f0'),
                cancelButtonColor: '#a8aaae',
                confirmButtonText: confirmText,
                cancelButtonText: 'Cancel'
            }).then(function (result) {
                return result.isConfirmed;
            });
        }

        return Promise.resolve(window.confirm(message));
    }

    // Direct Status Update function
    window.confirmDirectStatusUpdate = function (attendanceId, newStatus, workerName, dateStr) {
        var isPaid = newStatus === 'paid';
        var statusLabel = isPaid ? 'Paid' : 'Unpaid';
        var actionVerb = isPaid ? 'mark as Paid' : 'revert to Unpaid';
        var tone = isPaid ? 'primary' : 'warning';

        var title = isPaid ? 'Mark Attendance as Paid?' : 'Revert Attendance to Unpaid?';
        var message = 'Are you sure you want to ' + actionVerb + ' the shift for ' + workerName + ' on ' + dateStr + '?';

        askConfirmation({
            title: title,
            message: message,
            confirmText: 'Yes, ' + statusLabel,
            tone: tone
        }).then(function (confirmed) {
            if (confirmed) {
                sessionStorage.setItem('attendance_active_tab', 'shiftsPane');
                var form = document.getElementById('directStatusToggleForm');
                var statusInput = document.getElementById('directStatusInput');
                if (form && statusInput) {
                    form.action = '/attendance/' + attendanceId + '/payment-status';
                    statusInput.value = newStatus;
                    form.submit();
                }
            }
        });
    };

    // Bulk Actions & Checkbox Management
    var selectAllCheckbox = document.getElementById('selectAllShifts');
    var rowCheckboxes = document.querySelectorAll('.shift-select-checkbox');
    var bulkActionBar = document.getElementById('shiftsBulkActionBar');
    var countBadge = document.getElementById('selectedShiftsCountBadge');
    var countText = document.getElementById('selectedShiftsCountText');
    var btnDeselectAll = document.getElementById('btnDeselectAllShifts');
    var btnSelectAllAction = document.getElementById('btnSelectAllShiftsAction');
    var btnBulkMarkPaid = document.getElementById('btnBulkMarkPaid');
    var btnBulkMarkUnpaid = document.getElementById('btnBulkMarkUnpaid');

    function getSelectedCheckboxes() {
        return Array.from(document.querySelectorAll('.shift-select-checkbox:checked'));
    }

    function updateBulkToolbarState() {
        var selected = getSelectedCheckboxes();
        var total = rowCheckboxes.length;
        var selectedCount = selected.length;

        if (countBadge) countBadge.textContent = selectedCount;
        if (countText) {
            countText.textContent = selectedCount === 1 ? '1 record selected' : selectedCount + ' records selected';
        }

        if (selectedCount > 0) {
            if (bulkActionBar) {
                bulkActionBar.classList.remove('d-none');
                bulkActionBar.classList.add('d-flex');
            }
        } else {
            if (bulkActionBar) {
                bulkActionBar.classList.add('d-none');
                bulkActionBar.classList.remove('d-flex');
            }
        }

        // Update master selectAll checkbox
        if (selectAllCheckbox) {
            if (selectedCount === 0) {
                selectAllCheckbox.checked = false;
                selectAllCheckbox.indeterminate = false;
            } else if (selectedCount === total && total > 0) {
                selectAllCheckbox.checked = true;
                selectAllCheckbox.indeterminate = false;
            } else {
                selectAllCheckbox.checked = false;
                selectAllCheckbox.indeterminate = true;
            }
        }

        // Update row highlight
        rowCheckboxes.forEach(function (cb) {
            var row = document.getElementById('shiftRow_' + cb.value);
            if (row) {
                if (cb.checked) {
                    row.classList.add('table-active');
                } else {
                    row.classList.remove('table-active');
                }
            }
        });
    }

    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function () {
            var isChecked = this.checked;
            rowCheckboxes.forEach(function (cb) {
                cb.checked = isChecked;
            });
            updateBulkToolbarState();
        });
    }

    rowCheckboxes.forEach(function (cb) {
        cb.addEventListener('change', updateBulkToolbarState);
    });

    if (btnDeselectAll) {
        btnDeselectAll.addEventListener('click', function () {
            rowCheckboxes.forEach(function (cb) {
                cb.checked = false;
            });
            updateBulkToolbarState();
        });
    }

    if (btnSelectAllAction) {
        btnSelectAllAction.addEventListener('click', function () {
            rowCheckboxes.forEach(function (cb) {
                cb.checked = true;
            });
            updateBulkToolbarState();
        });
    }

    function executeBulkStatusChange(targetStatus) {
        var selected = getSelectedCheckboxes();
        if (selected.length === 0) {
            return;
        }

        var count = selected.length;
        var isPaid = targetStatus === 'paid';
        var statusLabel = isPaid ? 'Paid' : 'Unpaid';
        var title = isPaid
            ? 'Mark ' + count + ' Record(s) as Paid?'
            : 'Revert ' + count + ' Record(s) to Unpaid?';
        var message = 'Are you sure you want to mark ' + count + ' selected attendance record(s) as ' + statusLabel + '? This will update their payout tracking.';

        askConfirmation({
            title: title,
            message: message,
            confirmText: 'Yes, Mark ' + statusLabel,
            tone: isPaid ? 'primary' : 'warning'
        }).then(function (confirmed) {
            if (confirmed) {
                sessionStorage.setItem('attendance_active_tab', 'shiftsPane');
                var bulkForm = document.getElementById('bulkPaymentStatusForm');
                var bulkStatusInput = document.getElementById('bulkStatusInput');
                var container = document.getElementById('bulkAttendanceIdsContainer');

                if (bulkForm && bulkStatusInput && container) {
                    bulkStatusInput.value = targetStatus;
                    container.innerHTML = '';
                    selected.forEach(function (cb) {
                        var hiddenInput = document.createElement('input');
                        hiddenInput.type = 'hidden';
                        hiddenInput.name = 'attendance_ids[]';
                        hiddenInput.value = cb.value;
                        container.appendChild(hiddenInput);
                    });
                    bulkForm.submit();
                }
            }
        });
    }

    if (btnBulkMarkPaid) {
        btnBulkMarkPaid.addEventListener('click', function () {
            executeBulkStatusChange('paid');
        });
    }

    if (btnBulkMarkUnpaid) {
        btnBulkMarkUnpaid.addEventListener('click', function () {
            executeBulkStatusChange('unpaid');
        });
    }

    // Initial check
    updateBulkToolbarState();
});
</script>
@endpush

