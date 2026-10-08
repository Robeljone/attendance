<?php

namespace App\Enums;

enum SalaryIncrementType: string
{
    case Fixed = 'fixed';
    case Percent = 'percent';

    public function label(): string
    {
        return match ($this) {
            self::Fixed => 'Fixed amount',
            self::Percent => 'Percent of base',
        };
    }
}
