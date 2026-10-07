<?php

namespace App\Actions;

use App\Enums\PayrollPeriodStatus;
use App\Models\PayrollPeriod;
use InvalidArgumentException;

class SubmitPayrollAction
{
    public function handle(PayrollPeriod $period): PayrollPeriod
    {
        if (! $period->isDraft()) {
            throw new InvalidArgumentException('Only draft payroll can be submitted for approval.');
        }

        if ($period->payslips()->count() === 0) {
            throw new InvalidArgumentException('Generate payslips before submitting payroll.');
        }

        $period->update([
            'status' => PayrollPeriodStatus::PendingApproval,
            'submitted_at' => now(),
        ]);

        return $period->refresh();
    }
}
