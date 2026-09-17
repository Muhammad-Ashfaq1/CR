<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkerWageHistory extends Model
{
    use HasFactory;

    protected $table = 'worker_wage_history';

    protected $fillable = [
        'worker_id',
        'daily_wage',
        'effective_from',
        'reason',
        'changed_by',
    ];

    protected function casts(): array
    {
        return [
            'daily_wage' => 'decimal:2',
            'effective_from' => 'date',
        ];
    }

    public function worker(): BelongsTo
    {
        return $this->belongsTo(Worker::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
