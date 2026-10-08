<?php

namespace App\Models;

use Database\Factories\SalaryStructureFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalaryStructure extends Model
{
    /** @use HasFactory<SalaryStructureFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function payComponents(): BelongsToMany
    {
        return $this->belongsToMany(PayComponent::class)
            ->withPivot(['amount', 'is_enabled'])
            ->withTimestamps()
            ->orderBy('pay_components.sort_order');
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }
}
