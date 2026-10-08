<?php

namespace App\Models;

use App\Enums\SalaryIncrementStatus;
use App\Enums\SalaryIncrementType;
use Database\Factories\SalaryIncrementFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalaryIncrement extends Model
{
    /** @use HasFactory<SalaryIncrementFactory> */
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'type',
        'amount',
        'effective_date',
        'status',
        'notes',
        'previous_base_salary',
        'new_base_salary',
        'created_by',
        'applied_by',
        'applied_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => SalaryIncrementType::class,
            'status' => SalaryIncrementStatus::class,
            'amount' => 'decimal:2',
            'previous_base_salary' => 'decimal:2',
            'new_base_salary' => 'decimal:2',
            'effective_date' => 'date',
            'applied_at' => 'datetime',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function appliedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'applied_by');
    }

    public function isPending(): bool
    {
        return $this->status === SalaryIncrementStatus::Pending;
    }
}
