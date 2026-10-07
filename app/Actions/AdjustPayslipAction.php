<?php

namespace App\Actions;

use App\Enums\PayslipLineType;
use App\Models\Payslip;
use App\Models\PayslipLine;
use Illuminate\Support\Str;
use InvalidArgumentException;

class AdjustPayslipAction
{
    public function __construct(private RecalculatePayslipTotalsAction $recalculatePayslipTotals) {}

    /**
     * @param  array{type: string, label: string, amount: float|int|string}  $data
     */
    public function add(Payslip $payslip, array $data): PayslipLine
    {
        $this->ensureEditable($payslip);

        $type = PayslipLineType::from($data['type']);
        $label = trim((string) $data['label']);
        $code = 'manual_'.Str::slug($label, '_');
        if ($code === 'manual_' || $code === 'manual') {
            $code = 'manual_'.Str::lower(Str::random(6));
        }

        $maxSort = (int) $payslip->lines()->max('sort_order');

        $line = $payslip->lines()->create([
            'type' => $type,
            'code' => $code,
            'label' => $label,
            'amount' => round((float) $data['amount'], 2),
            'is_manual' => true,
            'is_taxable' => $type === PayslipLineType::Earning,
            'sort_order' => $maxSort + 1,
        ]);

        $this->recalculatePayslipTotals->handle($payslip->fresh());

        return $line;
    }

    public function remove(Payslip $payslip, PayslipLine $line): void
    {
        $this->ensureEditable($payslip);

        if ($line->payslip_id !== $payslip->id) {
            throw new InvalidArgumentException('Adjustment does not belong to this payslip.');
        }

        if (! $line->is_manual) {
            throw new InvalidArgumentException('Only manual adjustments can be removed.');
        }

        $line->delete();
        $this->recalculatePayslipTotals->handle($payslip->fresh());
    }

    private function ensureEditable(Payslip $payslip): void
    {
        $payslip->loadMissing('payrollPeriod');

        if (! $payslip->payrollPeriod?->isDraft()) {
            throw new InvalidArgumentException('Adjustments are only allowed on draft payroll.');
        }
    }
}
