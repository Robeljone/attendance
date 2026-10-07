<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\PayrollPeriod;
use App\Models\Payslip;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payslip>
 */
class PayslipFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $base = fake()->randomFloat(2, 2000, 8000);

        return [
            'payroll_period_id' => PayrollPeriod::factory(),
            'employee_id' => Employee::factory(),
            'base_salary' => $base,
            'present_days' => 20,
            'absent_days' => 0,
            'leave_days' => 0,
            'overtime_pay' => 0,
            'deductions' => 0,
            'bonuses' => 0,
            'net_pay' => $base,
            'breakdown' => [
                'gross' => $base,
                'deductions' => 0,
                'net' => $base,
            ],
        ];
    }
}
