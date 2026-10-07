<?php

namespace App\Http\Controllers\Admin;

use App\Actions\FinalizePayrollAction;
use App\Actions\GeneratePayrollAction;
use App\Actions\MarkPayrollPaidAction;
use App\Actions\SubmitPayrollAction;
use App\Enums\EmploymentStatus;
use App\Enums\PayrollPeriodStatus;
use App\Http\Controllers\Controller;
use App\Models\CompanySetting;
use App\Models\Employee;
use App\Models\PayrollPeriod;
use App\Support\ResolvesIndexPagination;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PayrollController extends Controller
{
    public function index(Request $request): View
    {
        $search = ResolvesIndexPagination::search($request);

        $periods = PayrollPeriod::query()
            ->withCount('payslips')
            ->when($search, function ($query) use ($search): void {
                $query->where('name', 'like', '%'.$search.'%');
            })
            ->latest()
            ->paginate(ResolvesIndexPagination::perPage($request))
            ->withQueryString();

        return view('admin.payroll.index', [
            'payrollPeriods' => $periods,
            'employees' => Employee::query()
                ->with('user')
                ->where('status', EmploymentStatus::Active)
                ->orderBy('employee_number')
                ->get(),
        ]);
    }

    public function create(): RedirectResponse
    {
        return redirect()->route('admin.payroll.index', ['modal' => 'create']);
    }

    public function store(Request $request, GeneratePayrollAction $generatePayroll): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'excluded_employee_ids' => ['nullable', 'array'],
            'excluded_employee_ids.*' => ['integer', 'exists:employees,id'],
        ]);

        $period = PayrollPeriod::query()->create([
            'name' => $validated['name'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'excluded_employee_ids' => $validated['excluded_employee_ids'] ?? [],
            'status' => PayrollPeriodStatus::Draft,
        ]);

        $generatePayroll->handle($period, $request->user());

        return redirect()->route('admin.payroll.show', $period)->with('success', __('Draft payroll generated. Review, adjust, then submit for approval.'));
    }

    public function show(PayrollPeriod $payroll): View
    {
        $payroll->load(['payslips.employee.user', 'payslips.lines', 'generator', 'approver']);

        return view('admin.payroll.show', [
            'payrollPeriod' => $payroll,
            'summary' => $payroll->summary(),
        ]);
    }

    public function regenerate(Request $request, PayrollPeriod $payroll, GeneratePayrollAction $generatePayroll): RedirectResponse
    {
        try {
            $generatePayroll->handle($payroll, $request->user());
        } catch (InvalidArgumentException $exception) {
            return back()->withErrors(['payroll' => $exception->getMessage()]);
        }

        return redirect()
            ->route('admin.payroll.show', $payroll)
            ->with('success', __('Payroll regenerated. Manual adjustments were kept.'));
    }

    public function submit(PayrollPeriod $payroll, SubmitPayrollAction $submitPayroll): RedirectResponse
    {
        try {
            $submitPayroll->handle($payroll);
        } catch (InvalidArgumentException $exception) {
            return back()->withErrors(['payroll' => $exception->getMessage()]);
        }

        return redirect()
            ->route('admin.payroll.show', $payroll)
            ->with('success', __('Payroll submitted for approval.'));
    }

    public function finalize(Request $request, PayrollPeriod $payroll, FinalizePayrollAction $finalizePayroll): RedirectResponse
    {
        try {
            $finalizePayroll->handle($payroll, $request->user());
        } catch (InvalidArgumentException $exception) {
            return back()->withErrors(['payroll' => $exception->getMessage()]);
        }

        return redirect()
            ->route('admin.payroll.show', $payroll)
            ->with('success', __('Payroll finalized. Employees can now view their payslips.'));
    }

    public function markPaid(PayrollPeriod $payroll, MarkPayrollPaidAction $markPayrollPaid): RedirectResponse
    {
        try {
            $markPayrollPaid->handle($payroll);
        } catch (InvalidArgumentException $exception) {
            return back()->withErrors(['payroll' => $exception->getMessage()]);
        }

        return redirect()
            ->route('admin.payroll.show', $payroll)
            ->with('success', __('Payroll marked as paid.'));
    }

    public function exportBankCsv(PayrollPeriod $payroll): StreamedResponse
    {
        abort_unless($payroll->isFinalized() || $payroll->isPaid(), 403);

        $payroll->load(['payslips.employee.user']);
        $filename = 'payroll-bank-'.str($payroll->name)->slug().'.csv';

        return response()->streamDownload(function () use ($payroll): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['employee_number', 'employee_name', 'bank_account', 'net_pay', 'currency']);

            $currency = CompanySetting::current()->currency ?? 'USD';

            foreach ($payroll->payslips as $payslip) {
                fputcsv($handle, [
                    $payslip->employee?->employee_number,
                    $payslip->employee?->user?->name,
                    $payslip->employee?->bank_account,
                    number_format((float) $payslip->net_pay, 2, '.', ''),
                    $currency,
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
