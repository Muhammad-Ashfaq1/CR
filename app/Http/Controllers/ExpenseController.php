<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Project;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ExpenseController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();
        $query = Expense::query()->with(['project', 'category', 'recordedBy']);

        if ($user->isOwner()) {
            $projectIds = Project::where('owner_id', $user->id)->pluck('id');
            $query->whereIn('project_id', $projectIds);
        }

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('category_id')) {
            $query->where('expense_category_id', $request->category_id);
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('expense_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('expense_date', '<=', $request->to_date);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('vendor', 'like', "%{$search}%");
            });
        }

        $totalExpense = (float) (clone $query)->sum('amount');
        $expenses = $query->latest('expense_date')->paginate(15)->withQueryString();

        $projects = $user->isOwner()
            ? Project::where('owner_id', $user->id)->get()
            : Project::all();

        $categories = ExpenseCategory::active()->get();
        $paymentMethods = ['Cash', 'Bank Transfer', 'Cheque', 'Online Payment', 'Other'];

        return view('expenses.index', compact(
            'expenses',
            'totalExpense',
            'projects',
            'categories',
            'paymentMethods'
        ));
    }

    public function create(Request $request): View
    {
        $user = auth()->user();
        $selectedProjectId = $request->query('project_id');

        $projects = $user->isOwner()
            ? Project::where('owner_id', $user->id)->get()
            : Project::all();

        $categories = ExpenseCategory::active()->get();
        $paymentMethods = ['Cash', 'Bank Transfer', 'Cheque', 'Online Payment', 'Other'];

        return view('expenses.create', compact('projects', 'categories', 'paymentMethods', 'selectedProjectId'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'project_id' => ['required', 'exists:projects,id'],
            'expense_category_id' => ['required', 'exists:expense_categories,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'expense_date' => ['required', 'date'],
            'vendor' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'payment_method' => ['nullable', 'string', 'max:50'],
            'receipt' => ['nullable', 'file', 'image', 'mimes:jpeg,png,jpg,gif,pdf', 'max:5120'],
            'notes' => ['nullable', 'string'],
        ]);

        $receiptPath = null;
        if ($request->hasFile('receipt')) {
            $receiptPath = $request->file('receipt')->store('receipts', 'public');
        }

        $expense = Expense::create([
            'project_id' => $validated['project_id'],
            'expense_category_id' => $validated['expense_category_id'],
            'recorded_by' => auth()->id(),
            'amount' => $validated['amount'],
            'expense_date' => $validated['expense_date'],
            'vendor' => $validated['vendor'] ?? null,
            'description' => $validated['description'] ?? null,
            'payment_method' => $validated['payment_method'] ?? 'Cash',
            'receipt_path' => $receiptPath,
            'notes' => $validated['notes'] ?? null,
        ]);

        $category = ExpenseCategory::find($validated['expense_category_id']);
        $project = Project::find($validated['project_id']);

        ActivityLogger::log([
            'project_id' => $project->id,
            'event' => 'expense_recorded',
            'description' => 'Recorded expense of PKR '.number_format($expense->amount)." for '{$category->name}' on project '{$project->name}'",
            'properties' => [
                'expense_id' => $expense->id,
                'category_id' => $category->id,
                'amount' => $expense->amount,
            ],
        ], $expense);

        return redirect()->route('expenses.index', ['project_id' => $project->id])
            ->with('success', 'Expense of PKR '.number_format($expense->amount, 2).' recorded successfully.');
    }

    public function show(Expense $expense): View
    {
        $expense->load(['project', 'category', 'recordedBy']);

        return view('expenses.show', compact('expense'));
    }

    public function edit(Expense $expense): View
    {
        $user = auth()->user();

        $projects = $user->isOwner()
            ? Project::where('owner_id', $user->id)->get()
            : Project::all();

        $categories = ExpenseCategory::active()->get();
        $paymentMethods = ['Cash', 'Bank Transfer', 'Cheque', 'Online Payment', 'Other'];

        return view('expenses.edit', compact('expense', 'projects', 'categories', 'paymentMethods'));
    }

    public function update(Request $request, Expense $expense): RedirectResponse
    {
        $validated = $request->validate([
            'project_id' => ['required', 'exists:projects,id'],
            'expense_category_id' => ['required', 'exists:expense_categories,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'expense_date' => ['required', 'date'],
            'vendor' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'payment_method' => ['nullable', 'string', 'max:50'],
            'receipt' => ['nullable', 'file', 'image', 'mimes:jpeg,png,jpg,gif,pdf', 'max:5120'],
            'notes' => ['nullable', 'string'],
        ]);

        $receiptPath = $expense->receipt_path;
        if ($request->hasFile('receipt')) {
            if ($receiptPath && Storage::disk('public')->exists($receiptPath)) {
                Storage::disk('public')->delete($receiptPath);
            }
            $receiptPath = $request->file('receipt')->store('receipts', 'public');
        }

        $expense->update([
            'project_id' => $validated['project_id'],
            'expense_category_id' => $validated['expense_category_id'],
            'amount' => $validated['amount'],
            'expense_date' => $validated['expense_date'],
            'vendor' => $validated['vendor'] ?? null,
            'description' => $validated['description'] ?? null,
            'payment_method' => $validated['payment_method'] ?? 'Cash',
            'receipt_path' => $receiptPath,
            'notes' => $validated['notes'] ?? null,
        ]);

        ActivityLogger::log([
            'project_id' => $expense->project_id,
            'event' => 'expense_updated',
            'description' => "Updated expense #{$expense->id} (PKR ".number_format($expense->amount).')',
            'properties' => ['expense_id' => $expense->id],
        ], $expense);

        return redirect()->route('expenses.index')
            ->with('success', 'Expense updated successfully.');
    }

    public function destroy(Expense $expense): RedirectResponse
    {
        $amount = $expense->amount;
        $projectId = $expense->project_id;

        if ($expense->receipt_path && Storage::disk('public')->exists($expense->receipt_path)) {
            Storage::disk('public')->delete($expense->receipt_path);
        }

        $expense->delete();

        ActivityLogger::log([
            'project_id' => $projectId,
            'event' => 'expense_deleted',
            'description' => 'Deleted expense of PKR '.number_format($amount),
        ]);

        return redirect()->route('expenses.index')
            ->with('success', 'Expense of PKR '.number_format($amount, 2).' deleted successfully.');
    }
}
