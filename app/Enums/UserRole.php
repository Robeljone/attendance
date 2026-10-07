<?php

namespace App\Enums;

enum UserRole: string
{
    case SuperAdmin = 'superadmin';
    case Admin = 'admin';
    case Hr = 'hr';
    case Manager = 'manager';
    case Employee = 'employee';

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super Admin',
            self::Admin => 'Admin',
            self::Hr => 'HR',
            self::Manager => 'Manager',
            self::Employee => 'Employee',
        };
    }

    public function isStaff(): bool
    {
        return in_array($this, [self::SuperAdmin, self::Admin, self::Hr, self::Manager], true);
    }

    public function canManageHr(): bool
    {
        return in_array($this, [self::SuperAdmin, self::Admin, self::Hr], true);
    }

    public function canManageBranding(): bool
    {
        return $this === self::SuperAdmin;
    }
}
