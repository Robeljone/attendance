<?php

namespace Database\Factories;

use App\Enums\EmployeeDocumentType;
use App\Models\Employee;
use App\Models\EmployeeDocument;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EmployeeDocument>
 */
class EmployeeDocumentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'type' => fake()->randomElement(EmployeeDocumentType::cases()),
            'title' => fake()->words(3, true),
            'file_path' => 'employee-documents/'.fake()->uuid().'.pdf',
            'original_name' => fake()->word().'.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => fake()->numberBetween(10_000, 2_000_000),
            'document_number' => fake()->optional()->bothify('DOC-####-??'),
            'issued_on' => fake()->optional()->date(),
            'expires_on' => fake()->optional()->dateTimeBetween('+1 year', '+5 years')?->format('Y-m-d'),
            'notes' => null,
        ];
    }
}
