<?php

namespace App\Enums;

enum AttendanceStatus: string
{
    case FullDay = 'full_day';
    case HalfDay = 'half_day';
    case Absent = 'absent';
    case Leave = 'leave';

    public function label(): string
    {
        return match ($this) {
            self::FullDay => 'Full Day',
            self::HalfDay => 'Half Day',
            self::Absent => 'Absent',
            self::Leave => 'Leave',
        };
    }

    public function multiplier(): float
    {
        return match ($this) {
            self::FullDay => 1.0,
            self::HalfDay => 0.5,
            self::Absent => 0.0,
            self::Leave => 0.0,
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::FullDay => 'bg-label-success',
            self::HalfDay => 'bg-label-warning',
            self::Absent => 'bg-label-danger',
            self::Leave => 'bg-label-secondary',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::FullDay => 'ti-check-circle',
            self::HalfDay => 'ti-circle-half',
            self::Absent => 'ti-x-circle',
            self::Leave => 'ti-calendar-off',
        };
    }
}
