<?php

namespace App\Models;

use App\Enums\AttendanceStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    use HasFactory;

    protected $table = 'attendance';

    protected $fillable = [
        'worker_id',
        'project_id',
        'recorded_by',
        'attendance_date',
        'status',
        'wage_at_time',
        'payable_amount',
        'notes',
        'is_paid',
        'paid_at',
        'payment_method',
        'payment_reference',
    ];

    protected function casts(): array
    {
        return [
            'attendance_date' => 'date',
            'status' => AttendanceStatus::class,
            'wage_at_time' => 'decimal:2',
            'payable_amount' => 'decimal:2',
            'is_paid' => 'boolean',
            'paid_at' => 'datetime',
        ];
    }

    public function scopeUnpaid($query)
    {
        return $query->where('is_paid', false);
    }

    public function scopePaid($query)
    {
        return $query->where('is_paid', true);
    }

    public function worker(): BelongsTo
    {
        return $this->belongsTo(Worker::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    /**
     * Calculate and set payable_amount based on status and wage_at_time.
     */
    public static function calculatePayable(AttendanceStatus $status, float $wageAtTime): float
    {
        return round($status->multiplier() * $wageAtTime, 2);
    }
}
