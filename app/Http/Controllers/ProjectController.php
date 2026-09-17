<?php

namespace App\Http\Controllers;

use App\Enums\PaymentType;
use App\Enums\ProjectStatus;
use App\Models\Contractor;
use App\Models\ExpenseCategory;
use App\Models\Project;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();
        $query = Project::query()->with(['owner', 'contractors'])->withCount(['workers', 'expenses']);

        if ($user->isOwner()) {
            $query->where('owner_id', $user->id);
        } elseif ($user->isContractor()) {
            $contractor = $user->contractorProfile;
            if ($contractor) {
                $query->whereHas('contractors', fn ($q) => $q->where('contractors.id', $contractor->id));
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('site_name', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            });
        }

        $projects = $query->latest()->paginate(10)->withQueryString();
        $statuses = ProjectStatus::cases();
        $owners = User::where('role', 'owner')->orWhere('role', 'admin')->get();
        $contractors = Contractor::where('is_active', true)->get();

        return view('projects.index', compact('projects', 'statuses', 'owners', 'contractors'));
    }

    public function create(): RedirectResponse
    {
        return redirect()->route('projects.index', ['action' => 'create']);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'site_name' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'owner_id' => ['nullable', 'exists:users,id'],
            'start_date' => ['nullable', 'date'],
            'expected_completion_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'status' => ['required', Rule::enum(ProjectStatus::class)],
            'notes' => ['nullable', 'string'],
            // Optional contractor assignment
            'contractor_id' => ['nullable', 'exists:contractors,id'],
            'contract_amount' => ['nullable', 'numeric', 'min:0'],
            'agreement_notes' => ['nullable', 'string'],
        ]);

        $ownerId = $user->isAdmin() && ! empty($validated['owner_id'])
            ? $validated['owner_id']
            : ($user->isOwner() ? $user->id : auth()->id());

        $project = Project::create([
            'name' => $validated['name'],
            'site_name' => $validated['site_name'] ?? null,
            'location' => $validated['location'] ?? null,
            'owner_id' => $ownerId,
            'start_date' => $validated['start_date'] ?? null,
            'expected_completion_date' => $validated['expected_completion_date'] ?? null,
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?? null,
        ]);

        if (! empty($validated['contractor_id'])) {
            $project->contractors()->attach($validated['contractor_id'], [
                'contract_amount' => $validated['contract_amount'] ?? 0,
                'is_primary' => true,
                'assigned_date' => now()->toDateString(),
                'agreement_notes' => $validated['agreement_notes'] ?? null,
            ]);
        }

        ActivityLogger::log([
            'project_id' => $project->id,
            'event' => 'project_created',
            'description' => "Created project '{$project->name}'",
            'properties' => ['project_id' => $project->id],
        ], $project);

        return redirect()->route('projects.show', $project)
            ->with('success', "Project '{$project->name}' created successfully.");
    }

    public function show(Project $project): View
    {
        $this->authorizeProjectAccess($project);

        $project->load([
            'owner',
            'contractors',
            'expenses.category',
            'contractorPayments.contractor',
            'workers' => fn ($q) => $q->where('status', 'active'),
        ]);

        // Financial calculations
        $totalContractAmount = $project->totalContractAmount();
        $totalPaidToContractors = $project->totalContractorPaid();
        $remainingContractorBalance = $project->contractorRemaining();

        $materialExpenses = $project->totalExpensesByType('material');
        $laborExpenses = $project->totalExpensesByType('labor');
        $equipmentExpenses = $project->totalExpensesByType('equipment');
        $utilitiesExpenses = $project->totalExpensesByType('utilities');
        $otherExpenses = (float) $project->expenses()
            ->whereHas('category', fn ($q) => $q->whereNotIn('type', ['material', 'labor', 'equipment', 'utilities']))
            ->sum('amount');

        $totalDirectExpenses = $project->totalExpenses();
        $totalProjectOutlay = $totalPaidToContractors + $totalDirectExpenses;

        $recentExpenses = $project->expenses()->with('category', 'recordedBy')->latest('expense_date')->take(5)->get();
        $recentPayments = $project->contractorPayments()->with('contractor', 'recordedBy')->latest('payment_date')->take(5)->get();
        $availableContractors = Contractor::where('is_active', true)
            ->whereNotIn('id', $project->contractors->pluck('id'))
            ->get();
        $categories = ExpenseCategory::active()->get();
        $paymentTypes = PaymentType::cases();
        $paymentMethods = ['Cash', 'Bank Transfer', 'Cheque', 'Online Payment', 'Other'];
        $statuses = ProjectStatus::cases();

        return view('projects.show', compact(
            'project',
            'totalContractAmount',
            'totalPaidToContractors',
            'remainingContractorBalance',
            'materialExpenses',
            'laborExpenses',
            'equipmentExpenses',
            'utilitiesExpenses',
            'otherExpenses',
            'totalDirectExpenses',
            'totalProjectOutlay',
            'recentExpenses',
            'recentPayments',
            'availableContractors',
            'categories',
            'paymentTypes',
            'paymentMethods',
            'statuses'
        ));
    }

    public function edit(Project $project): RedirectResponse
    {
        $this->authorizeProjectAccess($project);

        return redirect()->route('projects.index', ['edit' => $project->id]);
    }

    public function update(Request $request, Project $project): RedirectResponse
    {
        $this->authorizeProjectAccess($project);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'site_name' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'owner_id' => ['nullable', 'exists:users,id'],
            'start_date' => ['nullable', 'date'],
            'expected_completion_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'status' => ['required', Rule::enum(ProjectStatus::class)],
            'notes' => ['nullable', 'string'],
        ]);

        if (auth()->user()->isAdmin() && ! empty($validated['owner_id'])) {
            $project->owner_id = $validated['owner_id'];
        }

        $project->update([
            'name' => $validated['name'],
            'site_name' => $validated['site_name'] ?? null,
            'location' => $validated['location'] ?? null,
            'start_date' => $validated['start_date'] ?? null,
            'expected_completion_date' => $validated['expected_completion_date'] ?? null,
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?? null,
        ]);

        ActivityLogger::log([
            'project_id' => $project->id,
            'event' => 'project_updated',
            'description' => "Updated project '{$project->name}'",
            'properties' => ['project_id' => $project->id],
        ], $project);

        return redirect()->route('projects.show', $project)
            ->with('success', "Project '{$project->name}' updated successfully.");
    }

    public function destroy(Project $project): RedirectResponse
    {
        $this->authorizeProjectAccess($project);

        $name = $project->name;

        ActivityLogger::log([
            'project_id' => $project->id,
            'event' => 'project_deleted',
            'description' => "Deleted project '{$name}'",
            'properties' => ['project_id' => $project->id],
        ]);

        $project->delete();

        return redirect()->route('projects.index')
            ->with('success', "Project '{$name}' deleted successfully.");
    }

    public function assignContractor(Request $request, Project $project): RedirectResponse
    {
        $this->authorizeProjectAccess($project);

        $validated = $request->validate([
            'contractor_id' => ['required', 'exists:contractors,id'],
            'contract_amount' => ['required', 'numeric', 'min:0'],
            'is_primary' => ['nullable', 'boolean'],
            'agreement_notes' => ['nullable', 'string'],
        ]);

        $isPrimary = $request->boolean('is_primary');

        if ($isPrimary) {
            // Unset previous primary contractors
            $project->contractors()->updateExistingPivot(
                $project->contractors->pluck('id')->toArray(),
                ['is_primary' => false]
            );
        }

        $project->contractors()->syncWithoutDetaching([
            $validated['contractor_id'] => [
                'contract_amount' => $validated['contract_amount'],
                'is_primary' => $isPrimary,
                'assigned_date' => now()->toDateString(),
                'agreement_notes' => $validated['agreement_notes'] ?? null,
            ],
        ]);

        $contractor = Contractor::find($validated['contractor_id']);

        ActivityLogger::log([
            'project_id' => $project->id,
            'event' => 'contractor_assigned',
            'description' => "Assigned contractor {$contractor->name} with contract amount PKR ".number_format($validated['contract_amount']),
            'properties' => ['contractor_id' => $contractor->id, 'amount' => $validated['contract_amount']],
        ], $project);

        return redirect()->route('projects.show', $project)
            ->with('success', "Contractor {$contractor->name} assigned to project.");
    }

    public function removeContractor(Project $project, Contractor $contractor): RedirectResponse
    {
        $this->authorizeProjectAccess($project);

        $project->contractors()->detach($contractor->id);

        ActivityLogger::log([
            'project_id' => $project->id,
            'event' => 'contractor_removed',
            'description' => "Removed contractor {$contractor->name} from project '{$project->name}'",
            'properties' => ['contractor_id' => $contractor->id],
        ], $project);

        return redirect()->route('projects.show', $project)
            ->with('success', "Contractor {$contractor->name} removed from project.");
    }

    private function authorizeProjectAccess(Project $project): void
    {
        $user = auth()->user();

        if ($user->isAdmin()) {
            return;
        }

        if ($user->isOwner() && $project->owner_id === $user->id) {
            return;
        }

        if ($user->isContractor()) {
            $contractor = $user->contractorProfile;
            if ($contractor && $project->contractors()->where('contractors.id', $contractor->id)->exists()) {
                return;
            }
        }

        abort(403, 'Unauthorized access to this project.');
    }
}
