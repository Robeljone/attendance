<?php

namespace App\Enums;

enum SalaryIncrementStatus: string
{
    case Pending = 'pending';
    case Applied = 'applied';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Applied => 'Applied',
            self::Cancelled => 'Cancelled',
        };
    }
}
