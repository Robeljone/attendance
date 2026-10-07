<?php

namespace Database\Factories;

use App\Models\QrAttendanceToken;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<QrAttendanceToken>
 */
class QrAttendanceTokenFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'token' => Str::random(48),
            'station_name' => 'Main Entrance',
            'expires_at' => now()->addMinutes(5),
            'created_by' => null,
        ];
    }
}
