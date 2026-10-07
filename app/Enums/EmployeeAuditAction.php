<?php

namespace App\Enums;

enum EmployeeAuditAction: string
{
    case ClockIn = 'clock_in';
    case ClockOut = 'clock_out';
    case QrScan = 'qr_scan';
    case AttendanceBlocked = 'attendance_blocked';

    public function label(): string
    {
        return match ($this) {
            self::ClockIn => 'Clock in',
            self::ClockOut => 'Clock out',
            self::QrScan => 'QR scan',
            self::AttendanceBlocked => 'Attendance blocked',
        };
    }
}
