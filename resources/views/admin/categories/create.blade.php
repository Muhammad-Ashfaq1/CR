@extends('layouts.app')

@section('title', 'Add Category — Admin — ' . config('app.name'))

@section('content')
<div class="cst-page-header">
    <div>
        <h1 class="cst-page-title">Add Expense Category</h1>
        <p class="cst-page-subtitle">Create a category to group material purchases, labor wages, or site expenses.</p>
    </div>
    <div>
        <a href="{{ route('admin.expense-categories.index') }}" class="btn btn-outline-secondary">
            &larr; Back to Categories
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="cst-card">
            <div class="cst-card-body p-4">
                <form action="{{ route('admin.expense-categories.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label required">Category Name</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="e.g. Cement, Bricks, Steel, Fuel" required>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label required">Type</label>
                        <select name="type" class="form-select @error('type') is-invalid @enderror" required>
                            <option value="material" {{ old('type') == 'material' ? 'selected' : '' }}>Material</option>
                            <option value="labor" {{ old('type') == 'labor' ? 'selected' : '' }}>Labor / Workforce</option>
                            <option value="equipment" {{ old('type') == 'equipment' ? 'selected' : '' }}>Equipment / Machinery</option>
                            <option value="utilities" {{ old('type') == 'utilities' ? 'selected' : '' }}>Utilities (Water / Power / Fuel)</option>
                            <option value="subcontractor" {{ old('type') == 'subcontractor' ? 'selected' : '' }}>Subcontractor</option>
                            <option value="miscellaneous" {{ old('type') == 'miscellaneous' ? 'selected' : '' }}>Miscellaneous / Site Admin</option>
                        </select>
                        @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label">Tabler Icon</label>
                            <input type="text" name="icon" class="form-control" value="{{ old('icon', 'ti-receipt') }}" placeholder="ti-brick, ti-tool, etc.">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Color Code</label>
                            <input type="color" name="color" class="form-control form-control-color w-100" value="{{ old('color', '#f59e0b') }}">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Sort Order</label>
                        <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', 0) }}" min="0">
                    </div>

                    <div class="mb-4">
                        <div class="form-check form-switch">
                            <input type="hidden" name="is_active" value="0">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="isActiveCheck" {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="isActiveCheck">Category Active</label>
                        </div>
                    </div>

                    <div class="text-end border-top pt-3">
                        <button type="submit" class="btn btn-primary">Save Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
