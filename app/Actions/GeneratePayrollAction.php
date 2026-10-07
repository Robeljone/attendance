<?php

namespace App\Actions;

use App\Enums\EmploymentStatus;
use App\Enums\LeaveStatus;
use App\Models\AttendanceRecord;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\PayrollPeriod;
use App\Models\Payslip;
use App\Models\User;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;

class GeneratePayrollAction
{
    public function handle(PayrollPeriod $period, User $generator): PayrollPeriod
    {
        return DB::transaction(function () use ($period, $generator) {
            $period->payslips()->delete();

            $employees = Employee::query()
                ->where('status', EmploymentStatus::Active)
                ->with('user')
                ->get();

            $workDays = collect(CarbonPeriod::create($period->start_date, $period->end_date))
                ->filter(fn ($date) => $date->isWeekday())
                ->count();

            foreach ($employees as $employee) {
                $presentDays = AttendanceRecord::query()
                    ->where('employee_id', $employee->id)
                    ->whereBetween('work_date', [$period->start_date, $period->end_date])
                    ->whereNotNull('clock_in_at')
                    ->count();

                $leaveDays = LeaveRequest::query()
                    ->where('employee_id', $employee->id)
                    ->where('status', LeaveStatus::Approved)
                    ->where(function ($query) use ($period): void {
                        $query->whereBetween('start_date', [$period->start_date, $period->end_date])
                            ->orWhereBetween('end_date', [$period->start_date, $period->end_date]);
                    })
                    ->sum('days');

                $absentDays = max(0, $workDays - $presentDays - (int) $leaveDays);
                $dailyRate = $workDays > 0 ? ((float) $employee->base_salary / $workDays) : 0;
                $deductions = round($absentDays * $dailyRate, 2);
                $netPay = max(0, (float) $employee->base_salary - $deductions);

                Payslip::query()->create([
                    'payroll_period_id' => $period->id,
                    'employee_id' => $employee->id,
                    'base_salary' => $employee->base_salary,
                    'present_days' => $presentDays,
                    'absent_days' => $absentDays,
                    'leave_days' => (int) $leaveDays,
                    'overtime_pay' => 0,
                    'deductions' => $deductions,
                    'bonuses' => 0,
                    'net_pay' => $netPay,
                    'breakdown' => [
                        'work_days' => $workDays,
                        'daily_rate' => round($dailyRate, 2),
                    ],
                ]);
            }

            $period->update([
                'status' => 'processed',
                'generated_by' => $generator->id,
                'processed_at' => now(),
            ]);

            return $period->refresh()->load('payslips.employee.user');
        });
    }
}
