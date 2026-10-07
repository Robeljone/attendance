<?php

namespace App\Enums;

enum EmployeeAuditOutcome: string
{
    case Success = 'success';
    case Failed = 'failed';
    case Blocked = 'blocked';

    public function label(): string
    {
        return match ($this) {
            self::Success => 'Success',
            self::Failed => 'Failed',
            self::Blocked => 'Blocked',
        };
    }

    public function tone(): string
    {
        return match ($this) {
            self::Success => 'green',
            self::Failed => 'amber',
            self::Blocked => 'red',
        };
    }
}
