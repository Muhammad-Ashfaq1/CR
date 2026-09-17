@extends('layouts.app')

@section('title', 'Expense Categories — Admin — ' . config('app.name'))

@section('content')
<div class="awt-glass-card awt-tone-primary mb-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="avatar avatar-lg rounded-3 bg-label-primary d-flex align-items-center justify-content-center">
                <i class="icon-base ti tabler-category fs-2"></i>
            </div>
            <div>
                <h4 class="awt-dash-title mb-1">Expense Categories</h4>
                <p class="awt-dash-subtitle mb-0">Configure material, labor, equipment, and other expense categories for site accounting.</p>
            </div>
        </div>
        <div>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createCategoryModal">
                <i class="icon-base ti tabler-plus me-1"></i> Add Category
            </button>
        </div>
    </div>
</div>

<div class="awt-table-card awt-tone-secondary">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="border-bottom">
                <tr>
                    <th class="text-uppercase small fw-semibold text-muted ps-3">Category</th>
                    <th class="text-uppercase small fw-semibold text-muted">Type</th>
                    <th class="text-uppercase small fw-semibold text-muted">Icon</th>
                    <th class="text-uppercase small fw-semibold text-muted">Expenses Recorded</th>
                    <th class="text-uppercase small fw-semibold text-muted">Status</th>
                    <th class="text-uppercase small fw-semibold text-muted">Sort Order</th>
                    <th class="text-uppercase small fw-semibold text-muted text-end pe-3">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $cat)
                    <tr>
                        <td class="ps-3">
                            <div class="d-flex align-items-center">
                                <div class="rounded-3 d-flex align-items-center justify-content-center me-3" style="width: 36px; height: 36px; background-color: {{ $cat->color }}20; color: {{ $cat->color }};">
                                    <i class="icon-base ti {{ $cat->icon ?? 'tabler-receipt' }}"></i>
                                </div>
                                <div class="fw-semibold text-body">{{ $cat->name }}</div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-label-info text-uppercase">{{ $cat->type }}</span>
                        </td>
                        <td><code>{{ $cat->icon ?? 'tabler-receipt' }}</code></td>
                        <td>
                            <span class="badge bg-label-secondary">{{ $cat->expenses_count }} expenses</span>
                        </td>
                        <td>
                            @if($cat->is_active)
                                <span class="badge bg-label-success">Active</span>
                            @else
                                <span class="badge bg-label-secondary">Disabled</span>
                            @endif
                        </td>
                        <td>{{ $cat->sort_order }}</td>
                        <td class="text-end pe-3">
                            <button type="button" class="btn btn-sm btn-icon btn-text-secondary rounded-pill" data-bs-toggle="modal" data-bs-target="#editCategoryModal_{{ $cat->id }}" title="Edit Category">
                                <i class="icon-base ti tabler-edit"></i>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="p-0">
                            <div class="awt-empty-state">
                                <div class="awt-empty-state-icon text-muted mb-2">
                                    <i class="icon-base ti tabler-category fs-1"></i>
                                </div>
                                <h6 class="fw-semibold mb-1">No categories configured</h6>
                                <p class="text-muted small mb-0">Add your first expense category using the button above.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Create Category Modal --}}
<div class="modal fade" id="createCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white"><i class="icon-base ti tabler-category-plus me-2"></i> Add Expense Category</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.expense-categories.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label required fw-semibold">Category Name</label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Concrete & Cement" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label required fw-semibold">Classification Type</label>
                            <select name="type" class="form-select" required>
                                <option value="material">Material</option>
                                <option value="labor">Labor</option>
                                <option value="equipment">Equipment & Machinery</option>
                                <option value="utilities">Utilities & Fuel</option>
                                <option value="subcontractor">Subcontractor</option>
                                <option value="miscellaneous">Miscellaneous</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Icon Class</label>
                            <input type="text" name="icon" class="form-control" value="tabler-receipt" placeholder="tabler-receipt">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Badge Color</label>
                            <input type="color" name="color" class="form-control form-control-color w-100" value="#f59e0b">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Sort Order</label>
                            <input type="number" name="sort_order" class="form-control" value="0">
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input type="hidden" name="is_active" value="0">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="catActCr" checked>
                                <label class="form-check-label fw-semibold" for="catActCr">Category Active</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="icon-base ti tabler-check me-1"></i> Save Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Category Modals --}}
@foreach($categories as $cat)
<div class="modal fade" id="editCategoryModal_{{ $cat->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white"><i class="icon-base ti tabler-edit me-2"></i> Edit Category: {{ $cat->name }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.expense-categories.update', $cat) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label required fw-semibold">Category Name</label>
                            <input type="text" name="name" class="form-control" value="{{ $cat->name }}" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label required fw-semibold">Classification Type</label>
                            <select name="type" class="form-select" required>
                                <option value="material" {{ $cat->type === 'material' ? 'selected' : '' }}>Material</option>
                                <option value="labor" {{ $cat->type === 'labor' ? 'selected' : '' }}>Labor</option>
                                <option value="equipment" {{ $cat->type === 'equipment' ? 'selected' : '' }}>Equipment & Machinery</option>
                                <option value="utilities" {{ $cat->type === 'utilities' ? 'selected' : '' }}>Utilities & Fuel</option>
                                <option value="subcontractor" {{ $cat->type === 'subcontractor' ? 'selected' : '' }}>Subcontractor</option>
                                <option value="miscellaneous" {{ $cat->type === 'miscellaneous' ? 'selected' : '' }}>Miscellaneous</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Icon Class</label>
                            <input type="text" name="icon" class="form-control" value="{{ $cat->icon ?? 'tabler-receipt' }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Badge Color</label>
                            <input type="color" name="color" class="form-control form-control-color w-100" value="{{ $cat->color ?? '#f59e0b' }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Sort Order</label>
                            <input type="number" name="sort_order" class="form-control" value="{{ $cat->sort_order }}">
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input type="hidden" name="is_active" value="0">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="catActEd_{{ $cat->id }}" {{ $cat->is_active ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="catActEd_{{ $cat->id }}">Category Active</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-danger" onclick="confirmDelete(() => document.getElementById('delCatForm_{{ $cat->id }}').submit(), 'Delete category {{ $cat->name }}? Related expenses may be affected.')">
                        <i class="icon-base ti tabler-trash me-1"></i> Delete
                    </button>
                    <div>
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary"><i class="icon-base ti tabler-device-floppy me-1"></i> Save Changes</button>
                    </div>
                </div>
            </form>
            <form id="delCatForm_{{ $cat->id }}" action="{{ route('admin.expense-categories.destroy', $cat) }}" method="POST" class="d-none">
                @csrf
                @method('DELETE')
            </form>
        </div>
    </div>
</div>
@endforeach

@push('scripts')
@if(request('action') === 'create')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const m = document.getElementById('createCategoryModal');
        if (m) new bootstrap.Modal(m).show();
    });
</script>
@elseif(request('edit'))
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const m = document.getElementById('editCategoryModal_{{ request('edit') }}');
        if (m) new bootstrap.Modal(m).show();
    });
</script>
@endif
@endpush
@endsection
