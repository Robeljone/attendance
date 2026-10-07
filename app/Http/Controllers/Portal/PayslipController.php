<?php

namespace App\Http\Controllers\Portal;

use App\Enums\PayrollPeriodStatus;
use App\Http\Controllers\Controller;
use App\Models\CompanySetting;
use App\Models\Payslip;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PayslipController extends Controller
{
    public function index(Request $request): View
    {
        $employee = $request->user()->employee;
        abort_unless($employee, 403);

        $payslips = Payslip::query()
            ->with('payrollPeriod')
            ->where('employee_id', $employee->id)
            ->whereHas('payrollPeriod', function (Builder $query): void {
                $query->whereIn('status', [
                    PayrollPeriodStatus::Finalized,
                    PayrollPeriodStatus::Paid,
                ]);
            })
            ->latest()
            ->paginate(15);

        return view('portal.payslips.index', compact('payslips'));
    }

    public function show(Request $request, Payslip $payslip): View
    {
        $this->authorizeEmployeePayslip($request, $payslip);

        $payslip->load('payrollPeriod', 'employee.user', 'lines');

        return view('portal.payslips.show', compact('payslip'));
    }

    public function print(Request $request, Payslip $payslip): View
    {
        $this->authorizeEmployeePayslip($request, $payslip);

        $payslip->load('payrollPeriod', 'employee.user', 'employee.department', 'lines');

        return view('payslips.print', [
            'payslip' => $payslip,
            'company' => CompanySetting::current(),
            'backUrl' => route('portal.payslips.show', $payslip),
        ]);
    }

    private function authorizeEmployeePayslip(Request $request, Payslip $payslip): void
    {
        $employee = $request->user()->employee;
        abort_unless($employee && $payslip->employee_id === $employee->id, 403);

        $payslip->loadMissing('payrollPeriod');
        abort_unless($payslip->payrollPeriod?->isVisibleToEmployees(), 404);
    }
}
