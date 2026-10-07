<?php

namespace Database\Factories;

use App\Enums\EmploymentStatus;
use App\Enums\Gender;
use App\Enums\MaritalStatus;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Employee>
 */
class EmployeeFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'department_id' => null,
            'employee_number' => 'EMP-'.fake()->unique()->numerify('####'),
            'phone' => fake()->optional()->numerify('+1##########'),
            'position' => fake()->optional()->jobTitle(),
            'hire_date' => fake()->optional()->date(),
            'date_of_birth' => fake()->optional()->dateTimeBetween('-60 years', '-20 years')?->format('Y-m-d'),
            'address' => fake()->optional()->streetAddress(),
            'emergency_contact_name' => fake()->optional()->name(),
            'emergency_contact_phone' => fake()->optional()->numerify('+1##########'),
            'emergency_contact_relationship' => fake()->optional()->randomElement(['Spouse', 'Parent', 'Sibling', 'Friend']),
            'base_salary' => fake()->randomFloat(2, 2000, 9000),
            'bank_account' => fake()->optional()->iban(),
            'status' => EmploymentStatus::Active,
            'notes' => null,
            'gender' => fake()->optional()->randomElement(Gender::cases()),
            'marital_status' => fake()->optional()->randomElement(MaritalStatus::cases()),
            'nationality' => fake()->optional()->country(),
            'national_id' => fake()->optional()->numerify('############'),
            'tax_id' => fake()->optional()->numerify('TIN-########'),
            'personal_email' => fake()->optional()->safeEmail(),
            'blood_group' => fake()->optional()->randomElement(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-']),
        ];
    }
}
