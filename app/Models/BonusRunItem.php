<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BonusRunItem extends Model
{
    protected $fillable = [
        'bonus_run_id',
        'employee_id',
        'amount',
        'label',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    public function bonusRun(): BelongsTo
    {
        return $this->belongsTo(BonusRun::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
