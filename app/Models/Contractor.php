<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contractor extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'phone',
        'company_name',
        'cnic',
        'address',
        'is_active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'project_contractor')
            ->withPivot(['contract_amount', 'is_primary', 'assigned_date', 'agreement_notes'])
            ->withTimestamps();
    }

    public function payments(): HasMany
    {
        return $this->hasMany(ContractorPayment::class);
    }

    public function workers(): HasMany
    {
        return $this->hasMany(Worker::class);
    }

    public function totalPaidForProject(int $projectId): float
    {
        return (float) $this->payments()
            ->where('project_id', $projectId)
            ->where('is_voided', false)
            ->sum('amount');
    }
}
