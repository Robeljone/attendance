<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EmployeeAuditAction;
use App\Enums\EmployeeAuditOutcome;
use App\Enums\ExpenseClaimStatus;
use App\Enums\LeaveStatus;
use App\Enums\PayslipLineType;
use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\Employee;
use App\Models\EmployeeAuditLog;
use App\Models\ExpenseClaim;
use App\Models\LeaveRequest;
use App\Models\Payslip;
use App\Models\PayslipLine;
use App\Support\ResolvesIndexPagination;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $from = $request->date('from')?->toDateString() ?? now()->startOfMonth()->toDateString();
        $to = $request->date('to')?->toDateString() ?? now()->toDateString();

        $attendanceSummary = [
            'Total punches' => AttendanceRecord::query()->whereBetween('work_date', [$from, $to])->count(),
            'Employees present' => AttendanceRecord::query()->whereBetween('work_date', [$from, $to])->distinct('employee_id')->count('employee_id'),
            'Avg worked minutes' => (int) round((float) AttendanceRecord::query()->whereBetween('work_date', [$from, $to])->avg('worked_minutes')),
            'Pending leaves' => LeaveRequest::query()->where('status', LeaveStatus::Pending)->count(),
            'Approved leave days' => (int) LeaveRequest::query()
                ->where('status', LeaveStatus::Approved)
                ->whereBetween('start_date', [$from, $to])
                ->sum('days'),
        ];

        $topDepartment = Employee::query()
            ->select('departments.name', DB::raw('COUNT(attendance_records.id) as punches'))
            ->leftJoin('departments', 'departments.id', '=', 'employees.department_id')
            ->leftJoin('attendance_records', function ($join) use ($from, $to): void {
                $join->on('attendance_records.employee_id', '=', 'employees.id')
                    ->whereBetween('attendance_records.work_date', [$from, $to]);
            })
            ->groupBy('departments.name')
            ->orderByDesc('punches')
            ->first();

        $attendanceSummary['Top department'] = $topDepartment
            ? (($topDepartment->name ?? 'Unassigned').' ('.$topDepartment->punches.')')
            : '—';

        $payslipQuery = Payslip::query()
            ->whereHas('payrollPeriod', fn ($q) => $q->whereBetween('start_date', [$from, $to]));

        $payrollSummary = [
            'Total net pay' => number_format((float) (clone $payslipQuery)->sum('net_pay'), 2),
            'Total gross (earnings)' => number_format((float) (clone $payslipQuery)->sum(DB::raw('base_salary + bonuses + overtime_pay')), 2),
            'Total deductions' => number_format((float) (clone $payslipQuery)->sum('deductions'), 2),
            'Total overtime' => number_format((float) (clone $payslipQuery)->sum('overtime_pay'), 2),
            'Payslips issued' => (clone $payslipQuery)->count(),
            'Pending expense claims' => ExpenseClaim::query()->where('status', ExpenseClaimStatus::Pending)->count(),
        ];

        $payrollByDepartment = Payslip::query()
            ->select(
                DB::raw("COALESCE(departments.name, 'Unassigned') as department_name"),
                DB::raw('SUM(payslips.net_pay) as net_total'),
                DB::raw('SUM(payslips.overtime_pay) as overtime_total'),
                DB::raw('COUNT(payslips.id) as payslip_count'),
            )
            ->join('employees', 'employees.id', '=', 'payslips.employee_id')
            ->leftJoin('departments', 'departments.id', '=', 'employees.department_id')
            ->whereHas('payrollPeriod', fn ($q) => $q->whereBetween('start_date', [$from, $to]))
            ->groupBy('departments.name')
            ->orderByDesc('net_total')
            ->get();

        $deductionBreakdown = PayslipLine::query()
            ->select('code', 'label', DB::raw('SUM(amount) as total'))
            ->where('type', PayslipLineType::Deduction)
            ->whereHas('payslip.payrollPeriod', fn ($q) => $q->whereBetween('start_date', [$from, $to]))
            ->groupBy('code', 'label')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        $monthlyTrend = Payslip::query()
            ->with('payrollPeriod')
            ->whereHas('payrollPeriod', fn ($q) => $q->whereBetween('start_date', [$from, $to]))
            ->get()
            ->groupBy(fn (Payslip $payslip) => $payslip->payrollPeriod?->start_date?->format('Y-m') ?? 'unknown')
            ->map(fn ($group, $month) => (object) [
                'month_key' => $month,
                'net_total' => round($group->sum(fn (Payslip $payslip) => (float) $payslip->net_pay), 2),
                'overtime_total' => round($group->sum(fn (Payslip $payslip) => (float) $payslip->overtime_pay), 2),
                'deductions_total' => round($group->sum(fn (Payslip $payslip) => (float) $payslip->deductions), 2),
            ])
            ->sortKeys()
            ->values();

        $auditSummary = [
            'Attendance events' => EmployeeAuditLog::query()->whereBetween('created_at', [$from.' 00:00:00', $to.' 23:59:59'])->count(),
            'Network blocked' => EmployeeAuditLog::query()
                ->whereBetween('created_at', [$from.' 00:00:00', $to.' 23:59:59'])
                ->where('outcome', EmployeeAuditOutcome::Blocked)
                ->count(),
            'Off-network attempts' => EmployeeAuditLog::query()
                ->whereBetween('created_at', [$from.' 00:00:00', $to.' 23:59:59'])
                ->where('network_allowed', false)
                ->count(),
        ];

        return view('admin.reports.index', compact(
            'attendanceSummary',
            'payrollSummary',
            'payrollByDepartment',
            'deductionBreakdown',
            'monthlyTrend',
            'auditSummary',
            'from',
            'to',
        ));
    }

    public function audit(Request $request): View
    {
        $from = $request->date('from')?->toDateString() ?? now()->subDays(7)->toDateString();
        $to = $request->date('to')?->toDateString() ?? now()->toDateString();
        $search = ResolvesIndexPagination::search($request);

        $logs = EmployeeAuditLog::query()
            ->with(['employee.user', 'user'])
            ->whereBetween('created_at', [$from.' 00:00:00', $to.' 23:59:59'])
            ->when($request->filled('employee_id'), fn ($query) => $query->where('employee_id', $request->integer('employee_id')))
            ->when($request->filled('action'), function ($query) use ($request): void {
                $action = EmployeeAuditAction::tryFrom((string) $request->string('action'));
                if ($action) {
                    $query->where('action', $action);
                }
            })
            ->when($request->filled('outcome'), function ($query) use ($request): void {
                $outcome = EmployeeAuditOutcome::tryFrom((string) $request->string('outcome'));
                if ($outcome) {
                    $query->where('outcome', $outcome);
                }
            })
            ->when($request->filled('network'), function ($query) use ($request): void {
                if ($request->string('network')->toString() === 'allowed') {
                    $query->where('network_allowed', true);
                }

                if ($request->string('network')->toString() === 'not_allowed') {
                    $query->where('network_allowed', false);
                }
            })
            ->when($search, function ($query) use ($search): void {
                $query->where(function ($inner) use ($search): void {
                    $inner->where('ip_address', 'like', "%{$search}%")
                        ->orWhere('message', 'like', "%{$search}%")
                        ->orWhereHas('employee', function ($employeeQuery) use ($search): void {
                            $employeeQuery->where('employee_number', 'like', "%{$search}%")
                                ->orWhereHas('user', fn ($userQuery) => $userQuery->where('name', 'like', "%{$search}%"));
                        })
                        ->orWhereHas('user', fn ($userQuery) => $userQuery->where('name', 'like', "%{$search}%"));
                });
            })
            ->latest('id')
            ->paginate(ResolvesIndexPagination::perPage($request, 25))
            ->withQueryString();

        $employees = Employee::query()
            ->with('user')
            ->orderBy('employee_number')
            ->get();

        return view('admin.reports.audit', [
            'logs' => $logs,
            'employees' => $employees,
            'actions' => EmployeeAuditAction::cases(),
            'outcomes' => EmployeeAuditOutcome::cases(),
            'from' => $from,
            'to' => $to,
        ]);
    }
}
