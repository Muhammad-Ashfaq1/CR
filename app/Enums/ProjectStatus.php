<?php

namespace App\Enums;

enum ProjectStatus: string
{
    case Planning = 'planning';
    case Active = 'active';
    case OnHold = 'on_hold';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Planning => 'Planning',
            self::Active => 'Active',
            self::OnHold => 'On Hold',
            self::Completed => 'Completed',
            self::Cancelled => 'Cancelled',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Planning => 'bg-label-secondary',
            self::Active => 'bg-label-success',
            self::OnHold => 'bg-label-warning',
            self::Completed => 'bg-label-primary',
            self::Cancelled => 'bg-label-danger',
        };
    }
}
