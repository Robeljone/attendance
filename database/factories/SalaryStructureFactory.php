<?php

namespace Database\Factories;

use App\Models\SalaryStructure;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<SalaryStructure>
 */
class SalaryStructureFactory extends Factory
{
    protected $model = SalaryStructure::class;

    public function definition(): array
    {
        $name = fake()->unique()->jobTitle().' package';

        return [
            'name' => $name,
            'code' => Str::slug($name).'-'.fake()->unique()->numerify('###'),
            'description' => fake()->optional()->sentence(),
            'is_active' => true,
        ];
    }
}
