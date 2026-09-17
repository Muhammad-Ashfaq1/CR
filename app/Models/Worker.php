<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Worker extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'contractor_id',
        'project_id',
        'name',
        'phone',
        'worker_type',
        'daily_wage',
        'joining_date',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'daily_wage' => 'decimal:2',
            'joining_date' => 'date',
        ];
    }

    public function contractor(): BelongsTo
    {
        return $this->belongsTo(Contractor::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function attendance(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function wageHistory(): HasMany
    {
        return $this->hasMany(WorkerWageHistory::class)->orderBy('effective_from', 'desc');
    }

    /**
     * Get the effective wage for a specific date.
     */
    public function wageForDate(Carbon $date): float
    {
        $history = $this->wageHistory()
            ->where('effective_from', '<=', $date->toDateString())
            ->orderBy('effective_from', 'desc')
            ->first();

        return $history ? (float) $history->daily_wage : (float) $this->daily_wage;
    }

    /**
     * Total payable wages based on attendance records.
     */
    public function totalPayableWages(): float
    {
        return (float) $this->attendance()->sum('payable_amount');
    }

    public function scopeActive($query): Builder
    {
        return $query->where('status', 'active');
    }
}
