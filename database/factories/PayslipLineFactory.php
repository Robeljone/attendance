<?php

namespace Database\Factories;

use App\Enums\PayslipLineType;
use App\Models\Payslip;
use App\Models\PayslipLine;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PayslipLine>
 */
class PayslipLineFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'payslip_id' => Payslip::factory(),
            'type' => PayslipLineType::Earning,
            'code' => 'base_salary',
            'label' => 'Base salary',
            'amount' => fake()->randomFloat(2, 1000, 5000),
            'is_manual' => false,
            'sort_order' => 1,
        ];
    }
}
