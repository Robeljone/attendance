<?php

namespace App\Http\Controllers\Admin;

use App\Actions\AdjustPayslipAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePayslipAdjustmentRequest;
use App\Models\CompanySetting;
use App\Models\PayrollPeriod;
use App\Models\Payslip;
use App\Models\PayslipLine;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use InvalidArgumentException;

class PayslipController extends Controller
{
    public function show(PayrollPeriod $payroll, Payslip $payslip): View
    {
        $this->ensureBelongsToPeriod($payroll, $payslip);

        $payslip->load(['employee.user', 'employee.department', 'lines', 'payrollPeriod']);

        return view('admin.payroll.payslips.show', [
            'payrollPeriod' => $payroll,
            'payslip' => $payslip,
        ]);
    }

    public function print(PayrollPeriod $payroll, Payslip $payslip): View
    {
        $this->ensureBelongsToPeriod($payroll, $payslip);

        $payslip->load(['employee.user', 'employee.department', 'lines', 'payrollPeriod']);

        return view('payslips.print', [
            'payslip' => $payslip,
            'company' => CompanySetting::current(),
            'backUrl' => route('admin.payroll.payslips.show', [$payroll, $payslip]),
        ]);
    }

    public function storeAdjustment(
        StorePayslipAdjustmentRequest $request,
        PayrollPeriod $payroll,
        Payslip $payslip,
        AdjustPayslipAction $adjustPayslip,
    ): RedirectResponse {
        $this->ensureBelongsToPeriod($payroll, $payslip);

        try {
            $adjustPayslip->add($payslip, $request->validated());
        } catch (InvalidArgumentException $exception) {
            return back()->withErrors(['adjustment' => $exception->getMessage()]);
        }

        return redirect()
            ->route('admin.payroll.payslips.show', [$payroll, $payslip])
            ->with('success', __('Adjustment added.'));
    }

    public function destroyAdjustment(
        PayrollPeriod $payroll,
        Payslip $payslip,
        PayslipLine $line,
        AdjustPayslipAction $adjustPayslip,
    ): RedirectResponse {
        $this->ensureBelongsToPeriod($payroll, $payslip);

        try {
            $adjustPayslip->remove($payslip, $line);
        } catch (InvalidArgumentException $exception) {
            return back()->withErrors(['adjustment' => $exception->getMessage()]);
        }

        return redirect()
            ->route('admin.payroll.payslips.show', [$payroll, $payslip])
            ->with('success', __('Adjustment removed.'));
    }

    private function ensureBelongsToPeriod(PayrollPeriod $payroll, Payslip $payslip): void
    {
        abort_unless($payslip->payroll_period_id === $payroll->id, 404);
    }
}
