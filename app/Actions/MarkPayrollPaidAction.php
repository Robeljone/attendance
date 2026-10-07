<?php

namespace App\Actions;

use App\Enums\PayrollPeriodStatus;
use App\Models\PayrollPeriod;
use InvalidArgumentException;

class MarkPayrollPaidAction
{
    public function handle(PayrollPeriod $period): PayrollPeriod
    {
        if (! $period->isFinalized()) {
            throw new InvalidArgumentException('Only finalized payroll can be marked as paid.');
        }

        $period->update([
            'status' => PayrollPeriodStatus::Paid,
            'paid_at' => now(),
        ]);

        return $period->refresh();
    }
}
