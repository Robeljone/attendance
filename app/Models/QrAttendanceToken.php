<?php

namespace App\Models;

use Database\Factories\QrAttendanceTokenFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class QrAttendanceToken extends Model
{
    /** @use HasFactory<QrAttendanceTokenFactory> */
    use HasFactory;

    protected $fillable = [
        'token',
        'station_name',
        'expires_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isValid(): bool
    {
        return $this->expires_at->isFuture();
    }

    public static function issue(string $stationName, ?int $userId = null): self
    {
        return self::query()->create([
            'token' => Str::random(48),
            'station_name' => $stationName,
            'expires_at' => now()->addSeconds((int) config('attendance.qr_token_ttl_seconds', 60)),
            'created_by' => $userId,
        ]);
    }
}
