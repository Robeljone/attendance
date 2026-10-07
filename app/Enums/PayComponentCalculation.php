<?php

namespace App\Enums;

enum PayComponentCalculation: string
{
    case Fixed = 'fixed';
    case PercentOfBase = 'percent_of_base';

    public function label(): string
    {
        return match ($this) {
            self::Fixed => 'Fixed amount',
            self::PercentOfBase => 'Percent of base salary',
        };
    }
}
