<?php

namespace App\Actions;

use App\Enums\AttendanceMethod;
use App\Models\AttendanceRecord;
use App\Models\Employee;
use Illuminate\Validation\ValidationException;

class ClockOutAction
{
    public function handle(
        Employee $employee,
        AttendanceMethod $method,
        string $ip,
        ?string $notes = null,
    ): AttendanceRecord {
        $today = now()->toDateString();

        $record = AttendanceRecord::query()
            ->where('employee_id', $employee->id)
            ->whereDate('work_date', $today)
            ->first();

        if (! $record || ! $record->clock_in_at) {
            throw ValidationException::withMessages([
                'attendance' => 'You must clock in before clocking out.',
            ]);
        }

        if ($record->clock_out_at) {
            throw ValidationException::withMessages([
                'attendance' => 'You already clocked out today.',
            ]);
        }

        $clockOutAt = now();
        $workedMinutes = $record->clock_in_at->diffInMinutes($clockOutAt);

        $record->update([
            'clock_out_at' => $clockOutAt,
            'clock_out_method' => $method,
            'clock_out_ip' => $ip,
            'worked_minutes' => $workedMinutes,
            'notes' => $notes ?? $record->notes,
        ]);

        return $record->refresh();
    }
}
