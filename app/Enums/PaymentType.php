<?php

namespace App\Enums;

enum PaymentType: string
{
    case Advance = 'advance';
    case Installment = 'installment';
    case Final = 'final';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Advance => 'Advance',
            self::Installment => 'Installment',
            self::Final => 'Final Payment',
            self::Other => 'Other',
        };
    }
}
