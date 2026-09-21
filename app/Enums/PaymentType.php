<?php

namespace App\Enums;

enum PaymentType: string
{
    case Advance = 'advance';
    case Installment = 'installment';
    case LaborWage = 'labor_wage';
    case Final = 'final';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Advance => 'Advance',
            self::Installment => 'Installment',
            self::LaborWage => 'Labor Wages',
            self::Final => 'Final Payment',
            self::Other => 'Other',
        };
    }
}
