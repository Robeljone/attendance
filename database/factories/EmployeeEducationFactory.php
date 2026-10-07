<?php

namespace Database\Factories;

use App\Enums\EducationLevel;
use App\Models\Employee;
use App\Models\EmployeeEducation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EmployeeEducation>
 */
class EmployeeEducationFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startYear = fake()->numberBetween(2000, 2018);
        $endYear = $startYear + fake()->numberBetween(2, 5);

        return [
            'employee_id' => Employee::factory(),
            'institution' => fake()->company().' University',
            'level' => fake()->randomElement(EducationLevel::cases()),
            'field_of_study' => fake()->randomElement(['Computer Science', 'Business Administration', 'Accounting', 'Engineering', 'Human Resources']),
            'degree_title' => fake()->optional()->sentence(3),
            'start_year' => $startYear,
            'end_year' => $endYear,
            'grade' => fake()->optional()->randomElement(['3.2 GPA', 'First Class', 'Distinction', 'B+']),
            'is_highest' => false,
            'notes' => null,
        ];
    }

    public function highest(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_highest' => true,
        ]);
    }
}
