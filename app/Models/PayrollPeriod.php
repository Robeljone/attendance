<?php

namespace App\Models;

use App\Enums\PayrollPeriodStatus;
use Database\Factories\PayrollPeriodFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayrollPeriod extends Model
{
    /** @use HasFactory<PayrollPeriodFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'status',
        'excluded_employee_ids',
        'generated_by',
        'approved_by',
        'processed_at',
        'finalized_at',
        'submitted_at',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'status' => PayrollPeriodStatus::class,
            'excluded_employee_ids' => 'array',
            'processed_at' => 'datetime',
            'finalized_at' => 'datetime',
            'submitted_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    public function payslips(): HasMany
    {
        return $this->hasMany(Payslip::class);
    }

    public function generator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function isDraft(): bool
    {
        return $this->status === PayrollPeriodStatus::Draft;
    }

    public function isPendingApproval(): bool
    {
        return $this->status === PayrollPeriodStatus::PendingApproval;
    }

    public function isFinalized(): bool
    {
        return $this->status === PayrollPeriodStatus::Finalized;
    }

    public function isPaid(): bool
    {
        return $this->status === PayrollPeriodStatus::Paid;
    }

    public function isVisibleToEmployees(): bool
    {
        return $this->status?->isVisibleToEmployees() ?? false;
    }

    /**
     * @return array{headcount: int, gross: float, deductions: float, net: float, overtime: float}
     */
    public function summary(): array
    {
        $payslips = $this->relationLoaded('payslips')
            ? $this->payslips
            : $this->payslips()->get();

        return [
            'headcount' => $payslips->count(),
            'gross' => round($payslips->sum(fn (Payslip $payslip) => (float) ($payslip->breakdown['gross'] ?? $payslip->grossPay())), 2),
            'deductions' => round($payslips->sum(fn (Payslip $payslip) => (float) $payslip->deductions), 2),
            'net' => round($payslips->sum(fn (Payslip $payslip) => (float) $payslip->net_pay), 2),
            'overtime' => round($payslips->sum(fn (Payslip $payslip) => (float) $payslip->overtime_pay), 2),
        ];
    }
}
