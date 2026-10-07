<?php

namespace App\Actions;

use App\Enums\PayslipLineType;
use App\Models\CompanySetting;
use App\Models\Payslip;

class RecalculatePayslipTotalsAction
{
    public function handle(Payslip $payslip): Payslip
    {
        $payslip->loadMissing('lines');

        $payslip->lines()
            ->where('is_manual', false)
            ->whereIn('code', ['income_tax', 'pension'])
            ->delete();

        $payslip->unsetRelation('lines');
        $payslip->load('lines');

        $taxableEarnings = $payslip->lines
            ->where('type', PayslipLineType::Earning)
            ->where('is_taxable', true)
            ->sum(fn ($line) => (float) $line->amount);

        $settings = CompanySetting::current();
        $maxSort = (int) $payslip->lines->max('sort_order');

        $pensionPercent = max(0, (float) ($settings->pension_percent ?? 0));
        $taxPercent = max(0, (float) ($settings->income_tax_percent ?? 0));

        if ($pensionPercent > 0 && $taxableEarnings > 0) {
            $payslip->lines()->create([
                'type' => PayslipLineType::Deduction,
                'code' => 'pension',
                'label' => 'Pension ('.$pensionPercent.'%)',
                'amount' => round($taxableEarnings * ($pensionPercent / 100), 2),
                'is_manual' => false,
                'is_taxable' => false,
                'sort_order' => ++$maxSort,
            ]);
        }

        if ($taxPercent > 0 && $taxableEarnings > 0) {
            $payslip->lines()->create([
                'type' => PayslipLineType::Deduction,
                'code' => 'income_tax',
                'label' => 'Income tax ('.$taxPercent.'%)',
                'amount' => round($taxableEarnings * ($taxPercent / 100), 2),
                'is_manual' => false,
                'is_taxable' => false,
                'sort_order' => ++$maxSort,
            ]);
        }

        $payslip->unsetRelation('lines');
        $payslip->load('lines');

        $earnings = $payslip->lines
            ->where('type', PayslipLineType::Earning)
            ->sum(fn ($line) => (float) $line->amount);

        $deductions = $payslip->lines
            ->where('type', PayslipLineType::Deduction)
            ->sum(fn ($line) => (float) $line->amount);

        $baseSalary = (float) ($payslip->lines
            ->firstWhere('code', 'base_salary')
            ?->amount ?? $payslip->base_salary);

        $overtimePay = (float) ($payslip->lines
            ->firstWhere('code', 'overtime')
            ?->amount ?? 0);

        $bonuses = $payslip->lines
            ->where('type', PayslipLineType::Earning)
            ->reject(fn ($line) => in_array($line->code, ['base_salary', 'overtime'], true))
            ->sum(fn ($line) => (float) $line->amount);

        $breakdown = is_array($payslip->breakdown) ? $payslip->breakdown : [];

        $payslip->update([
            'base_salary' => round($baseSalary, 2),
            'overtime_pay' => round($overtimePay, 2),
            'bonuses' => round($bonuses, 2),
            'deductions' => round($deductions, 2),
            'net_pay' => round(max(0, $earnings - $deductions), 2),
            'breakdown' => array_merge($breakdown, [
                'gross' => round($earnings, 2),
                'taxable_earnings' => round($taxableEarnings, 2),
                'deductions' => round($deductions, 2),
                'net' => round(max(0, $earnings - $deductions), 2),
            ]),
        ]);

        return $payslip->refresh()->load('lines');
    }
}
