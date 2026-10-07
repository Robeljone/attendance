<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Payslip;
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
            ->latest()
            ->paginate(15);

        return view('portal.payslips.index', compact('payslips'));
    }

    public function show(Request $request, Payslip $payslip): View
    {
        $employee = $request->user()->employee;
        abort_unless($employee && $payslip->employee_id === $employee->id, 403);

        $payslip->load('payrollPeriod', 'employee.user');

        return view('portal.payslips.show', compact('payslip'));
    }
}
