<?php

namespace App\Models;

use Database\Factories\PayslipFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payslip extends Model
{
    /** @use HasFactory<PayslipFactory> */
    use HasFactory;

    protected $fillable = [
        'payroll_period_id',
        'employee_id',
        'base_salary',
        'present_days',
        'absent_days',
        'leave_days',
        'overtime_pay',
        'deductions',
        'bonuses',
        'net_pay',
        'breakdown',
    ];

    protected function casts(): array
    {
        return [
            'base_salary' => 'decimal:2',
            'overtime_pay' => 'decimal:2',
            'deductions' => 'decimal:2',
            'bonuses' => 'decimal:2',
            'net_pay' => 'decimal:2',
            'breakdown' => 'array',
        ];
    }

    public function payrollPeriod(): BelongsTo
    {
        return $this->belongsTo(PayrollPeriod::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
