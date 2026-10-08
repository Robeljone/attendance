<?php

namespace App\Models;

use App\Enums\BonusRunStatus;
use Database\Factories\BonusRunFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BonusRun extends Model
{
    /** @use HasFactory<BonusRunFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'payroll_period_id',
        'status',
        'notes',
        'created_by',
        'applied_by',
        'applied_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => BonusRunStatus::class,
            'applied_at' => 'datetime',
        ];
    }

    public function payrollPeriod(): BelongsTo
    {
        return $this->belongsTo(PayrollPeriod::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(BonusRunItem::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function appliedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'applied_by');
    }

    public function isDraft(): bool
    {
        return $this->status === BonusRunStatus::Draft;
    }
}
