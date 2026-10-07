<?php

namespace App\Enums;

enum PayrollPeriodStatus: string
{
    case Draft = 'draft';
    case PendingApproval = 'pending_approval';
    case Finalized = 'finalized';
    case Paid = 'paid';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::PendingApproval => 'Pending approval',
            self::Finalized => 'Finalized',
            self::Paid => 'Paid',
        };
    }

    public function isEditable(): bool
    {
        return $this === self::Draft;
    }

    public function isVisibleToEmployees(): bool
    {
        return in_array($this, [self::Finalized, self::Paid], true);
    }
}
