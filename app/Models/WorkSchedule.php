<?php

namespace App\Models;

use Database\Factories\WorkScheduleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class WorkSchedule extends Model
{
    /** @use HasFactory<WorkScheduleFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'start_time',
        'end_time',
        'work_days',
        'break_minutes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'work_days' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function employees(): BelongsToMany
    {
        return $this->belongsToMany(Employee::class)
            ->withPivot(['effective_from', 'effective_to'])
            ->withTimestamps();
    }
}
