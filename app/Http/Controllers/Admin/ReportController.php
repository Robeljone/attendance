<?php

namespace App\Http\Controllers\Admin;

use App\Enums\LeaveStatus;
use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\Payslip;
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

        $payrollSummary = [
            'Total net pay' => number_format((float) Payslip::query()
                ->whereHas('payrollPeriod', fn ($q) => $q->whereBetween('start_date', [$from, $to]))
                ->sum('net_pay'), 2),
            'Total deductions' => number_format((float) Payslip::query()
                ->whereHas('payrollPeriod', fn ($q) => $q->whereBetween('start_date', [$from, $to]))
                ->sum('deductions'), 2),
            'Payslips issued' => Payslip::query()
                ->whereHas('payrollPeriod', fn ($q) => $q->whereBetween('start_date', [$from, $to]))
                ->count(),
        ];

        return view('admin.reports.index', compact('attendanceSummary', 'payrollSummary', 'from', 'to'));
    }
}
