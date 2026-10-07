<?php

namespace App\Http\Controllers\Admin;

use App\Actions\GeneratePayrollAction;
use App\Http\Controllers\Controller;
use App\Models\PayrollPeriod;
use App\Support\ResolvesIndexPagination;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

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

        return view('admin.payroll.index', ['payrollPeriods' => $periods]);
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
        ]);

        $period = PayrollPeriod::query()->create([
            ...$validated,
            'status' => 'draft',
        ]);

        $generatePayroll->handle($period, $request->user());

        return redirect()->route('admin.payroll.show', $period)->with('success', 'Payroll generated.');
    }

    public function show(PayrollPeriod $payroll): View
    {
        $payroll->load(['payslips.employee.user', 'generator']);

        return view('admin.payroll.show', ['payrollPeriod' => $payroll]);
    }
}
