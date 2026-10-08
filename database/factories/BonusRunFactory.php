<?php

namespace Database\Factories;

use App\Enums\BonusRunStatus;
use App\Models\BonusRun;
use App\Models\PayrollPeriod;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BonusRun>
 */
class BonusRunFactory extends Factory
{
    protected $model = BonusRun::class;

    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true).' bonus',
            'payroll_period_id' => PayrollPeriod::factory(),
            'status' => BonusRunStatus::Draft,
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
