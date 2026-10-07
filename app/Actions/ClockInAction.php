<?php

namespace App\Actions;

use App\Enums\AttendanceMethod;
use App\Enums\EmploymentStatus;
use App\Models\AttendanceRecord;
use App\Models\Employee;
use Illuminate\Validation\ValidationException;

class ClockInAction
{
    public function handle(
        Employee $employee,
        AttendanceMethod $method,
        string $ip,
        ?string $notes = null,
    ): AttendanceRecord {
        if ($employee->status !== EmploymentStatus::Active) {
            throw ValidationException::withMessages([
                'attendance' => 'Only active employees can clock in.',
            ]);
        }

        $today = now()->toDateString();

        $existing = AttendanceRecord::query()
            ->where('employee_id', $employee->id)
            ->whereDate('work_date', $today)
            ->first();

        if ($existing?->clock_in_at) {
            throw ValidationException::withMessages([
                'attendance' => 'You already clocked in today.',
            ]);
        }

        return AttendanceRecord::query()->updateOrCreate(
            [
                'employee_id' => $employee->id,
                'work_date' => $today,
            ],
            [
                'clock_in_at' => now(),
                'clock_in_method' => $method,
                'clock_in_ip' => $ip,
                'notes' => $notes,
            ],
        );
    }
}
