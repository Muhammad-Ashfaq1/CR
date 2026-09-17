<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExpenseCategory;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ExpenseCategoryController extends Controller
{
    public function index(): View
    {
        $categories = ExpenseCategory::withCount('expenses')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('admin.categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('admin.categories.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:expense_categories,name'],
            'type' => ['required', 'string', 'in:material,labor,equipment,utilities,subcontractor,miscellaneous'],
            'icon' => ['nullable', 'string', 'max:100'],
            'color' => ['nullable', 'string', 'max:50'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $category = ExpenseCategory::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'type' => $validated['type'],
            'icon' => $validated['icon'] ?? 'ti-receipt',
            'color' => $validated['color'] ?? '#f59e0b',
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active', true),
        ]);

        ActivityLogger::log([
            'event' => 'category_created',
            'description' => "Created expense category {$category->name}",
            'properties' => ['category_id' => $category->id],
        ], $category);

        return redirect()->route('admin.expense-categories.index')
            ->with('success', "Expense category '{$category->name}' created.");
    }

    public function edit(ExpenseCategory $expenseCategory): View
    {
        return view('admin.categories.edit', compact('expenseCategory'));
    }

    public function update(Request $request, ExpenseCategory $expenseCategory): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('expense_categories')->ignore($expenseCategory->id)],
            'type' => ['required', 'string', 'in:material,labor,equipment,utilities,subcontractor,miscellaneous'],
            'icon' => ['nullable', 'string', 'max:100'],
            'color' => ['nullable', 'string', 'max:50'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $expenseCategory->update([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'type' => $validated['type'],
            'icon' => $validated['icon'] ?? $expenseCategory->icon,
            'color' => $validated['color'] ?? $expenseCategory->color,
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active', true),
        ]);

        ActivityLogger::log([
            'event' => 'category_updated',
            'description' => "Updated expense category {$expenseCategory->name}",
            'properties' => ['category_id' => $expenseCategory->id],
        ], $expenseCategory);

        return redirect()->route('admin.expense-categories.index')
            ->with('success', "Expense category '{$expenseCategory->name}' updated.");
    }

    public function destroy(ExpenseCategory $expenseCategory): RedirectResponse
    {
        if ($expenseCategory->expenses()->exists()) {
            return redirect()->route('admin.expense-categories.index')
                ->with('error', "Cannot delete '{$expenseCategory->name}' because it is linked to existing expenses.");
        }

        $categoryName = $expenseCategory->name;
        $expenseCategory->delete();

        ActivityLogger::log([
            'event' => 'category_deleted',
            'description' => "Deleted expense category {$categoryName}",
        ]);

        return redirect()->route('admin.expense-categories.index')
            ->with('success', "Expense category '{$categoryName}' deleted.");
    }
}
