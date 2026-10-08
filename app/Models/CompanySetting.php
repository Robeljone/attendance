<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class CompanySetting extends Model
{
    protected $fillable = [
        'company_name',
        'tagline',
        'logo_path',
        'favicon_path',
        'primary_color',
        'support_email',
        'timezone',
        'allowed_ip_cidrs',
        'enforce_company_network',
        'currency',
        'income_tax_percent',
        'pension_percent',
        'standard_work_hours_per_day',
        'overtime_weekday_multiplier',
        'overtime_weekend_multiplier',
        'late_grace_minutes',
        'late_penalty_per_occurrence',
        'deduct_unexcused_absence',
    ];

    protected function casts(): array
    {
        return [
            'allowed_ip_cidrs' => 'array',
            'enforce_company_network' => 'boolean',
            'income_tax_percent' => 'decimal:2',
            'pension_percent' => 'decimal:2',
            'standard_work_hours_per_day' => 'decimal:2',
            'overtime_weekday_multiplier' => 'decimal:2',
            'overtime_weekend_multiplier' => 'decimal:2',
            'late_grace_minutes' => 'integer',
            'late_penalty_per_occurrence' => 'decimal:2',
            'deduct_unexcused_absence' => 'boolean',
        ];
    }

    public static function current(): self
    {
        return static::query()->firstOrCreate([], [
            'company_name' => config('app.name', 'Attendance HR'),
            'timezone' => config('app.timezone', 'UTC'),
            'allowed_ip_cidrs' => config('attendance.allowed_ip_cidrs', []),
            'enforce_company_network' => (bool) config('attendance.enforce_company_network', true),
            'currency' => 'USD',
        ]);
    }

    public function logoUrl(): ?string
    {
        return $this->publicUrl($this->logo_path);
    }

    public function faviconUrl(): ?string
    {
        return $this->publicUrl($this->favicon_path);
    }

    private function publicUrl(?string $path): ?string
    {
        if (! filled($path)) {
            return null;
        }

        return Storage::disk('public')->url($path);
    }
}
