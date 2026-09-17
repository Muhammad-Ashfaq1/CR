@extends('layouts.app')

@section('title', 'Expense Categories — Admin — ' . config('app.name'))

@section('content')
<div class="cst-page-header">
    <div>
        <h1 class="cst-page-title">Expense Categories</h1>
        <p class="cst-page-subtitle">Configure material, labor, equipment, and other expense categories.</p>
    </div>
    <div>
        <a href="{{ route('admin.expense-categories.create') }}" class="btn btn-primary">
            <i class="ti ti-plus me-1"></i> Add Category
        </a>
    </div>
</div>

<div class="cst-card">
    <div class="cst-card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Category</th>
                        <th>Type</th>
                        <th>Icon</th>
                        <th>Expenses Recorded</th>
                        <th>Status</th>
                        <th>Sort Order</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $cat)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 36px; height: 36px; background-color: {{ $cat->color }}20; color: {{ $cat->color }};">
                                        <i class="ti {{ $cat->icon ?? 'ti-receipt' }}"></i>
                                    </div>
                                    <div class="fw-semibold text-dark">{{ $cat->name }}</div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border text-uppercase">{{ $cat->type }}</span>
                            </td>
                            <td><code>{{ $cat->icon ?? 'ti-receipt' }}</code></td>
                            <td>
                                <span class="badge bg-label-info">{{ $cat->expenses_count }} expenses</span>
                            </td>
                            <td>
                                @if($cat->is_active)
                                    <span class="badge bg-label-success">Active</span>
                                @else
                                    <span class="badge bg-label-secondary">Disabled</span>
                                @endif
                            </td>
                            <td>{{ $cat->sort_order }}</td>
                            <td class="text-end">
                                <a href="{{ route('admin.expense-categories.edit', $cat) }}" class="btn btn-sm btn-icon btn-outline-primary" title="Edit">
                                    <i class="ti ti-edit"></i>
                                </a>
                                <form action="{{ route('admin.expense-categories.destroy', $cat) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Are you sure?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-icon btn-outline-danger" title="Delete">
                                        <i class="ti ti-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">No categories configured.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
