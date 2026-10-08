<?php

namespace Database\Factories;

use App\Enums\SalaryIncrementStatus;
use App\Enums\SalaryIncrementType;
use App\Models\Employee;
use App\Models\SalaryIncrement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SalaryIncrement>
 */
class SalaryIncrementFactory extends Factory
{
    protected $model = SalaryIncrement::class;

    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'type' => SalaryIncrementType::Fixed,
            'amount' => fake()->randomFloat(2, 50, 500),
            'effective_date' => now()->toDateString(),
            'status' => SalaryIncrementStatus::Pending,
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
