<?php

namespace Database\Factories;

use App\Enums\EmployeeAuditAction;
use App\Enums\EmployeeAuditOutcome;
use App\Models\Employee;
use App\Models\EmployeeAuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EmployeeAuditLog>
 */
class EmployeeAuditLogFactory extends Factory
{
    protected $model = EmployeeAuditLog::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'user_id' => User::factory(),
            'action' => EmployeeAuditAction::ClockIn,
            'outcome' => EmployeeAuditOutcome::Success,
            'ip_address' => '127.0.0.1',
            'network_allowed' => true,
            'network_enforced' => true,
            'message' => 'Clocked in successfully.',
            'context' => [],
        ];
    }

    public function blocked(): static
    {
        return $this->state(fn (): array => [
            'action' => EmployeeAuditAction::AttendanceBlocked,
            'outcome' => EmployeeAuditOutcome::Blocked,
            'network_allowed' => false,
            'network_enforced' => true,
            'message' => 'Blocked: not on company network.',
        ]);
    }
}
