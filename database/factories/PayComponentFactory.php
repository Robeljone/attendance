<?php

namespace Database\Factories;

use App\Enums\PayComponentCalculation;
use App\Enums\PayslipLineType;
use App\Models\PayComponent;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<PayComponent>
 */
class PayComponentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true).' Allowance';

        return [
            'name' => Str::title($name),
            'code' => Str::snake($name).'_'.fake()->unique()->numerify('##'),
            'type' => PayslipLineType::Earning,
            'calculation' => PayComponentCalculation::Fixed,
            'default_amount' => fake()->randomFloat(2, 50, 500),
            'is_taxable' => true,
            'is_active' => true,
            'sort_order' => fake()->numberBetween(0, 100),
        ];
    }
}
