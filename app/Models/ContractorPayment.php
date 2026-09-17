<?php

namespace App\Models;

use App\Enums\PaymentType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContractorPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'contractor_id',
        'recorded_by',
        'amount',
        'payment_date',
        'payment_type',
        'reference',
        'notes',
        'is_voided',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'payment_date' => 'date',
            'payment_type' => PaymentType::class,
            'is_voided' => 'boolean',
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

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function scopeActive($query): Builder
    {
        return $query->where('is_voided', false);
    }
}
