<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ProjectContractor extends Pivot
{
    protected $table = 'project_contractor';

    protected $fillable = [
        'project_id',
        'contractor_id',
        'contract_amount',
        'is_primary',
        'assigned_date',
        'agreement_notes',
    ];

    protected function casts(): array
    {
        return [
            'contract_amount' => 'decimal:2',
            'is_primary' => 'boolean',
            'assigned_date' => 'date',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function contractor(): BelongsTo
    {
        return $this->belongsTo(Contractor::class);
    }
}
