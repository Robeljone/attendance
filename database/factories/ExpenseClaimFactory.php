<?php

namespace Database\Factories;

use App\Enums\ExpenseClaimStatus;
use App\Models\Employee;
use App\Models\ExpenseClaim;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ExpenseClaim>
 */
class ExpenseClaimFactory extends Factory
{
    protected $model = ExpenseClaim::class;

    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'category' => fake()->randomElement(['travel', 'meals', 'medical', 'office', 'other']),
            'title' => fake()->sentence(3),
            'description' => fake()->optional()->sentence(),
            'amount' => fake()->randomFloat(2, 10, 400),
            'expense_date' => now()->subDays(fake()->numberBetween(0, 10))->toDateString(),
            'status' => ExpenseClaimStatus::Pending,
        ];
    }
}
