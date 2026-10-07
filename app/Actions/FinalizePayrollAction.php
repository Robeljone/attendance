<?php

namespace App\Actions;

use App\Enums\PayrollPeriodStatus;
use App\Models\PayrollPeriod;
use App\Models\User;
use InvalidArgumentException;

class FinalizePayrollAction
{
    public function handle(PayrollPeriod $period, ?User $approver = null): PayrollPeriod
    {
        if (! $period->isPendingApproval() && ! $period->isDraft()) {
            throw new InvalidArgumentException('Only draft or pending payroll can be finalized.');
        }

        if ($period->payslips()->count() === 0) {
            throw new InvalidArgumentException('Generate payslips before finalizing payroll.');
        }

        $period->update([
            'status' => PayrollPeriodStatus::Finalized,
            'finalized_at' => now(),
            'processed_at' => now(),
            'submitted_at' => $period->submitted_at ?? now(),
            'approved_by' => $approver?->id ?? $period->approved_by,
        ]);

        return $period->refresh();
    }
}
