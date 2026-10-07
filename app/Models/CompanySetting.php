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
    ];

    protected function casts(): array
    {
        return [
            'allowed_ip_cidrs' => 'array',
            'enforce_company_network' => 'boolean',
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
