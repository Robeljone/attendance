<?php

namespace App\Enums;

enum BonusRunStatus: string
{
    case Draft = 'draft';
    case Applied = 'applied';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Applied => 'Applied',
        };
    }
}
