<?php

namespace App\Enums;

enum AttendanceMethod: string
{
    case Qr = 'qr';
    case Manual = 'manual';
    case Portal = 'portal';

    public function label(): string
    {
        return match ($this) {
            self::Qr => 'QR Code',
            self::Manual => 'Manual',
            self::Portal => 'Portal',
        };
    }
}
