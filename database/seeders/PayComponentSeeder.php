<?php

namespace Database\Seeders;

use App\Enums\PayComponentCalculation;
use App\Enums\PayslipLineType;
use App\Models\PayComponent;
use Illuminate\Database\Seeder;

class PayComponentSeeder extends Seeder
{
    public function run(): void
    {
        $components = [
            [
                'name' => 'Housing allowance',
                'code' => 'housing_allowance',
                'type' => PayslipLineType::Earning,
                'calculation' => PayComponentCalculation::Fixed,
                'default_amount' => 0,
                'is_taxable' => true,
                'is_active' => true,
                'sort_order' => 10,
            ],
            [
                'name' => 'Transport allowance',
                'code' => 'transport_allowance',
                'type' => PayslipLineType::Earning,
                'calculation' => PayComponentCalculation::Fixed,
                'default_amount' => 0,
                'is_taxable' => true,
                'is_active' => true,
                'sort_order' => 20,
            ],
        ];

        foreach ($components as $component) {
            PayComponent::query()->updateOrCreate(
                ['code' => $component['code']],
                $component
            );
        }
    }
}
