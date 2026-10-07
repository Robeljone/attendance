<?php

namespace App\Models;

use App\Enums\PayComponentCalculation;
use App\Enums\PayslipLineType;
use Database\Factories\PayComponentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PayComponent extends Model
{
    /** @use HasFactory<PayComponentFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'type',
        'calculation',
        'default_amount',
        'is_taxable',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'type' => PayslipLineType::class,
            'calculation' => PayComponentCalculation::class,
            'default_amount' => 'decimal:2',
            'is_taxable' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function employees(): BelongsToMany
    {
        return $this->belongsToMany(Employee::class)
            ->withPivot(['amount', 'is_enabled'])
            ->withTimestamps();
    }
}
