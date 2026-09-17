<?php

namespace App\Models;

use App\Enums\ProjectStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'site_name',
        'location',
        'owner_id',
        'start_date',
        'expected_completion_date',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'expected_completion_date' => 'date',
            'status' => ProjectStatus::class,
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function contractors(): BelongsToMany
    {
        return $this->belongsToMany(Contractor::class, 'project_contractor')
            ->withPivot(['contract_amount', 'is_primary', 'assigned_date', 'agreement_notes'])
            ->withTimestamps();
    }

    public function primaryContractor(): BelongsToMany
    {
        return $this->contractors()->wherePivot('is_primary', true);
    }

    public function contractorPayments(): HasMany
    {
        return $this->hasMany(ContractorPayment::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function workers(): HasMany
    {
        return $this->hasMany(Worker::class);
    }

    public function attendance(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    /**
     * Get the total contract amount across all contractors.
     */
    public function totalContractAmount(): float
    {
        return (float) $this->contractors()->sum('project_contractor.contract_amount');
    }

    /**
     * Get the total amount paid to contractors.
     */
    public function totalContractorPaid(): float
    {
        return (float) $this->contractorPayments()->where('is_voided', false)->sum('amount');
    }

    /**
     * Get the remaining contractor balance.
     */
    public function contractorRemaining(): float
    {
        return $this->totalContractAmount() - $this->totalContractorPaid();
    }

    /**
     * Get total expenses grouped by category type.
     */
    public function totalExpensesByType(string $type): float
    {
        return (float) $this->expenses()
            ->whereHas('category', fn ($q) => $q->where('type', $type))
            ->sum('amount');
    }

    /**
     * Get total project expenses (all categories).
     */
    public function totalExpenses(): float
    {
        return (float) $this->expenses()->sum('amount');
    }
}
