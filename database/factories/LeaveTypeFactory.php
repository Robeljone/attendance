<?php

namespace Database\Factories;

use App\Models\LeaveType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LeaveType>
 */
class LeaveTypeFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true).' Leave',
            'code' => strtoupper(fake()->unique()->bothify('LV###')),
            'default_days_per_year' => fake()->numberBetween(0, 20),
            'is_paid' => true,
            'is_active' => true,
        ];
    }

    public function sick(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Sick Leave',
            'code' => 'SICK',
            'default_days_per_year' => 10,
            'is_paid' => true,
            'is_active' => true,
        ]);
    }
}
