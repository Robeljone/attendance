<?php

namespace Database\Factories;

use App\Enums\PayrollPeriodStatus;
use App\Models\PayrollPeriod;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PayrollPeriod>
 */
class PayrollPeriodFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start = now()->startOfMonth();

        return [
            'name' => $start->format('F Y'),
            'start_date' => $start->toDateString(),
            'end_date' => $start->copy()->endOfMonth()->toDateString(),
            'status' => PayrollPeriodStatus::Draft,
            'generated_by' => null,
            'processed_at' => null,
            'finalized_at' => null,
        ];
    }

    public function finalized(): static
    {
        return $this->state(fn () => [
            'status' => PayrollPeriodStatus::Finalized,
            'processed_at' => now(),
            'finalized_at' => now(),
        ]);
    }
}
