<?php

namespace App\Actions;

use App\Enums\BonusRunStatus;
use App\Enums\PayrollPeriodStatus;
use App\Enums\PayslipLineType;
use App\Models\BonusRun;
use App\Models\Payslip;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ApplyBonusRunAction
{
    public function __construct(private RecalculatePayslipTotalsAction $recalculatePayslipTotals) {}

    public function handle(BonusRun $bonusRun, User $actor): BonusRun
    {
        if (! $bonusRun->isDraft()) {
            throw new InvalidArgumentException('Only draft bonus runs can be applied.');
        }

        if (! $bonusRun->payroll_period_id) {
            throw new InvalidArgumentException('Assign a draft payroll period before applying a bonus run.');
        }

        $bonusRun->loadMissing(['items', 'payrollPeriod']);

        $period = $bonusRun->payrollPeriod;

        if (! $period || $period->status !== PayrollPeriodStatus::Draft) {
            throw new InvalidArgumentException('Bonuses can only be applied to a draft payroll period.');
        }

        return DB::transaction(function () use ($bonusRun, $actor, $period) {
            foreach ($bonusRun->items as $item) {
                if ((float) $item->amount <= 0) {
                    continue;
                }

                $payslip = Payslip::query()
                    ->where('payroll_period_id', $period->id)
                    ->where('employee_id', $item->employee_id)
                    ->first();

                if (! $payslip) {
                    continue;
                }

                $code = 'bonus_run_'.$bonusRun->id;
                $label = $item->label ?: ($bonusRun->name.' bonus');

                $payslip->lines()
                    ->where('code', $code)
                    ->where('is_manual', false)
                    ->delete();

                $maxSort = (int) $payslip->lines()->max('sort_order');

                $payslip->lines()->create([
                    'type' => PayslipLineType::Earning,
                    'code' => $code,
                    'label' => $label,
                    'amount' => round((float) $item->amount, 2),
                    'is_manual' => false,
                    'is_taxable' => true,
                    'sort_order' => $maxSort + 1,
                ]);

                $this->recalculatePayslipTotals->handle($payslip->fresh(['lines']));
            }

            $bonusRun->update([
                'status' => BonusRunStatus::Applied,
                'applied_by' => $actor->id,
                'applied_at' => now(),
            ]);

            return $bonusRun->refresh()->load(['items.employee.user', 'payrollPeriod']);
        });
    }
}
