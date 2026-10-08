<?php

namespace App\Actions;

use App\Enums\BonusRunStatus;
use App\Enums\EmploymentStatus;
use App\Enums\ExpenseClaimStatus;
use App\Enums\LeaveStatus;
use App\Enums\PayComponentCalculation;
use App\Enums\PayrollPeriodStatus;
use App\Enums\PayslipLineType;
use App\Models\AttendanceRecord;
use App\Models\BonusRun;
use App\Models\CompanySetting;
use App\Models\Employee;
use App\Models\ExpenseClaim;
use App\Models\LeaveRequest;
use App\Models\PayComponent;
use App\Models\PayrollPeriod;
use App\Models\Payslip;
use App\Models\User;
use App\Models\WorkSchedule;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class GeneratePayrollAction
{
    public function __construct(private RecalculatePayslipTotalsAction $recalculatePayslipTotals) {}

    public function handle(PayrollPeriod $period, User $generator): PayrollPeriod
    {
        if (! $period->isDraft()) {
            throw new InvalidArgumentException('Only draft payroll can be regenerated.');
        }

        return DB::transaction(function () use ($period, $generator) {
            $manualLines = $this->captureManualLines($period);

            ExpenseClaim::query()
                ->whereIn('payslip_id', $period->payslips()->pluck('id'))
                ->update([
                    'payslip_id' => null,
                    'status' => ExpenseClaimStatus::Approved,
                ]);

            $period->payslips()->delete();

            $excluded = collect($period->excluded_employee_ids ?? [])
                ->map(fn ($id) => (int) $id)
                ->filter()
                ->all();

            $employees = Employee::query()
                ->with(['user', 'workSchedules', 'payComponents'])
                ->where(function ($query) use ($period): void {
                    $query->where('status', EmploymentStatus::Active)
                        ->orWhere(function ($inner) use ($period): void {
                            $inner->whereNotNull('termination_date')
                                ->whereDate('termination_date', '>=', $period->start_date)
                                ->where(function ($hire) use ($period): void {
                                    $hire->whereNull('hire_date')
                                        ->orWhereDate('hire_date', '<=', $period->end_date);
                                });
                        });
                })
                ->where(function ($query) use ($period): void {
                    $query->whereNull('hire_date')
                        ->orWhereDate('hire_date', '<=', $period->end_date);
                })
                ->when($excluded !== [], fn ($query) => $query->whereNotIn('id', $excluded))
                ->get();

            foreach ($employees as $employee) {
                $payslip = $this->createPayslipForEmployee($period, $employee);

                foreach ($manualLines->get($employee->id, collect()) as $manualLine) {
                    $payslip->lines()->create([
                        'type' => $manualLine['type'],
                        'code' => $manualLine['code'],
                        'label' => $manualLine['label'],
                        'amount' => $manualLine['amount'],
                        'is_manual' => true,
                        'is_taxable' => $manualLine['is_taxable'],
                        'sort_order' => $manualLine['sort_order'],
                    ]);
                }

                $this->recalculatePayslipTotals->handle($payslip->fresh(['lines']));
            }

            $period->update([
                'status' => PayrollPeriodStatus::Draft,
                'generated_by' => $generator->id,
                'processed_at' => now(),
                'finalized_at' => null,
                'submitted_at' => null,
                'paid_at' => null,
                'approved_by' => null,
            ]);

            return $period->refresh()->load(['payslips.employee.user', 'payslips.lines']);
        });
    }

    /**
     * @return Collection<int, Collection<int, array{type: string, code: string, label: string, amount: float, is_taxable: bool, sort_order: int}>>
     */
    private function captureManualLines(PayrollPeriod $period): Collection
    {
        return $period->payslips()
            ->with(['lines' => fn ($query) => $query->where('is_manual', true)])
            ->get()
            ->mapWithKeys(function (Payslip $payslip) {
                return [
                    $payslip->employee_id => $payslip->lines->map(fn ($line) => [
                        'type' => $line->type->value,
                        'code' => $line->code,
                        'label' => $line->label,
                        'amount' => (float) $line->amount,
                        'is_taxable' => (bool) $line->is_taxable,
                        'sort_order' => (int) $line->sort_order,
                    ])->values(),
                ];
            });
    }

    private function createPayslipForEmployee(PayrollPeriod $period, Employee $employee): Payslip
    {
        $schedule = $employee->currentSchedule();
        $periodWorkDates = $this->workDatesInPeriod($period, $schedule);
        $payableWorkDates = $this->payableWorkDates($periodWorkDates, $employee);
        $periodWorkDays = $periodWorkDates->count();
        $payableWorkDays = $payableWorkDates->count();
        $prorationFactor = $periodWorkDays > 0 ? ($payableWorkDays / $periodWorkDays) : 0.0;

        $payableDateKeys = $payableWorkDates
            ->map(fn (CarbonInterface $date) => $date->toDateString())
            ->flip();

        $attendanceRecords = AttendanceRecord::query()
            ->where('employee_id', $employee->id)
            ->whereBetween('work_date', [$period->start_date, $period->end_date])
            ->whereNotNull('clock_in_at')
            ->get()
            ->filter(fn (AttendanceRecord $record) => $payableDateKeys->has($record->work_date->toDateString()))
            ->values();

        $presentDays = $attendanceRecords->count();

        [$paidLeaveDays, $unpaidLeaveDays] = $this->leaveDaysInPeriod($employee, $period, $payableWorkDates);
        $leaveDays = $paidLeaveDays + $unpaidLeaveDays;
        $absentDays = max(0, $payableWorkDays - $presentDays - $leaveDays);

        $settings = CompanySetting::current();
        $fullBaseSalary = round((float) $employee->base_salary, 2);
        $baseSalary = round($fullBaseSalary * $prorationFactor, 2);
        $dailyRate = $payableWorkDays > 0 ? round($baseSalary / $payableWorkDays, 2) : 0.0;
        $overtimePay = $this->calculateOvertimePay($attendanceRecords, $dailyRate, $settings);
        $lateOccurrences = $this->countLateOccurrences($attendanceRecords, $schedule, $settings);
        $latePenalty = round($lateOccurrences * max(0, (float) ($settings->late_penalty_per_occurrence ?? 0)), 2);
        $deductAbsence = (bool) ($settings->deduct_unexcused_absence ?? true);
        $absenceDeduction = $deductAbsence ? round($absentDays * $dailyRate, 2) : 0.0;
        $unpaidLeaveDeduction = round($unpaidLeaveDays * $dailyRate, 2);

        $payslip = Payslip::query()->create([
            'payroll_period_id' => $period->id,
            'employee_id' => $employee->id,
            'base_salary' => $baseSalary,
            'present_days' => $presentDays,
            'absent_days' => $absentDays,
            'leave_days' => $leaveDays,
            'overtime_pay' => $overtimePay,
            'deductions' => 0,
            'bonuses' => 0,
            'net_pay' => 0,
            'breakdown' => [
                'work_days' => $periodWorkDays,
                'payable_work_days' => $payableWorkDays,
                'proration_factor' => round($prorationFactor, 4),
                'daily_rate' => $dailyRate,
                'paid_leave_days' => $paidLeaveDays,
                'unpaid_leave_days' => $unpaidLeaveDays,
                'late_occurrences' => $lateOccurrences,
                'late_penalty' => $latePenalty,
            ],
        ]);

        $sort = 0;
        $this->addLine($payslip, PayslipLineType::Earning, 'base_salary', 'Base salary', $baseSalary, ++$sort, true);

        foreach ($this->resolveEmployeeComponents($employee) as $componentData) {
            /** @var PayComponent $component */
            $component = $componentData['component'];
            $amount = $this->resolveComponentAmount($component, (float) $componentData['amount'], $fullBaseSalary, $prorationFactor);

            if ($amount <= 0) {
                continue;
            }

            $this->addLine(
                $payslip,
                $component->type,
                $component->code,
                $component->name,
                $amount,
                ++$sort,
                $component->type === PayslipLineType::Earning ? (bool) $component->is_taxable : false,
            );
        }

        if ($overtimePay > 0) {
            $this->addLine($payslip, PayslipLineType::Earning, 'overtime', 'Overtime', $overtimePay, ++$sort, true);
        }

        foreach ($this->bonusLinesForEmployee($period, $employee) as $bonusLine) {
            $this->addLine(
                $payslip,
                PayslipLineType::Earning,
                $bonusLine['code'],
                $bonusLine['label'],
                $bonusLine['amount'],
                ++$sort,
                true,
            );
        }

        $expenseClaims = $this->approvedExpenseClaimsForEmployee($period, $employee);

        foreach ($expenseClaims as $claim) {
            $this->addLine(
                $payslip,
                PayslipLineType::Earning,
                'expense_claim_'.$claim->id,
                'Expense: '.$claim->title,
                (float) $claim->amount,
                ++$sort,
                false,
            );
        }

        if ($absenceDeduction > 0) {
            $this->addLine($payslip, PayslipLineType::Deduction, 'absence', 'Absence deduction (LOP)', $absenceDeduction, ++$sort, false);
        }

        if ($unpaidLeaveDeduction > 0) {
            $this->addLine($payslip, PayslipLineType::Deduction, 'unpaid_leave', 'Unpaid leave deduction', $unpaidLeaveDeduction, ++$sort, false);
        }

        if ($latePenalty > 0) {
            $this->addLine($payslip, PayslipLineType::Deduction, 'late_penalty', 'Late penalty', $latePenalty, ++$sort, false);
        }

        $payslip = $payslip->load('lines');

        if ($expenseClaims->isNotEmpty()) {
            ExpenseClaim::query()
                ->whereIn('id', $expenseClaims->pluck('id'))
                ->update([
                    'payslip_id' => $payslip->id,
                    'status' => ExpenseClaimStatus::Paid,
                ]);
        }

        return $payslip;
    }

    /**
     * @return Collection<int, array{component: PayComponent, amount: float}>
     */
    private function resolveEmployeeComponents(Employee $employee): Collection
    {
        $assigned = $employee->payComponents
            ->filter(fn (PayComponent $component) => (bool) ($component->pivot->is_enabled ?? true))
            ->keyBy('code');

        if ($assigned->isNotEmpty()) {
            return $assigned->map(fn (PayComponent $component) => [
                'component' => $component,
                'amount' => (float) $component->pivot->amount,
            ])->values();
        }

        // Legacy fallback for employees not yet synced to pay components.
        return PayComponent::query()
            ->where('is_active', true)
            ->whereIn('code', ['housing_allowance', 'transport_allowance'])
            ->orderBy('sort_order')
            ->get()
            ->map(function (PayComponent $component) use ($employee) {
                $amount = match ($component->code) {
                    'housing_allowance' => (float) ($employee->housing_allowance ?? 0),
                    'transport_allowance' => (float) ($employee->transport_allowance ?? 0),
                    default => 0.0,
                };

                return [
                    'component' => $component,
                    'amount' => $amount,
                ];
            })
            ->filter(fn (array $row) => $row['amount'] > 0)
            ->values();
    }

    private function resolveComponentAmount(
        PayComponent $component,
        float $configuredAmount,
        float $fullBaseSalary,
        float $prorationFactor,
    ): float {
        if ($component->calculation === PayComponentCalculation::PercentOfBase) {
            return round($fullBaseSalary * ($configuredAmount / 100) * $prorationFactor, 2);
        }

        return round($configuredAmount * $prorationFactor, 2);
    }

    /**
     * @return Collection<int, CarbonInterface>
     */
    private function workDatesInPeriod(PayrollPeriod $period, ?WorkSchedule $schedule): Collection
    {
        $allowedDays = is_array($schedule?->work_days) && $schedule->work_days !== []
            ? collect($schedule->work_days)->map(fn ($day) => (int) $day)->all()
            : [1, 2, 3, 4, 5];

        return collect(CarbonPeriod::create($period->start_date, $period->end_date))
            ->filter(fn (CarbonInterface $date) => in_array($date->dayOfWeekIso, $allowedDays, true))
            ->values();
    }

    /**
     * @param  Collection<int, CarbonInterface>  $workDates
     * @return Collection<int, CarbonInterface>
     */
    private function payableWorkDates(Collection $workDates, Employee $employee): Collection
    {
        return $workDates
            ->filter(function (CarbonInterface $date) use ($employee) {
                if ($employee->hire_date && $date->lt($employee->hire_date->startOfDay())) {
                    return false;
                }

                if ($employee->termination_date && $date->gt($employee->termination_date->endOfDay())) {
                    return false;
                }

                return true;
            })
            ->values();
    }

    /**
     * @param  Collection<int, CarbonInterface>  $workDates
     * @return array{0: int, 1: int}
     */
    private function leaveDaysInPeriod(Employee $employee, PayrollPeriod $period, Collection $workDates): array
    {
        $workDateKeys = $workDates->map(fn (CarbonInterface $date) => $date->toDateString())->flip();

        $leaves = LeaveRequest::query()
            ->with('leaveType')
            ->where('employee_id', $employee->id)
            ->where('status', LeaveStatus::Approved)
            ->whereDate('start_date', '<=', $period->end_date)
            ->whereDate('end_date', '>=', $period->start_date)
            ->get();

        $paidLeaveDays = 0;
        $unpaidLeaveDays = 0;

        foreach ($leaves as $leave) {
            $overlapStart = $leave->start_date->greaterThan($period->start_date)
                ? $leave->start_date
                : $period->start_date;
            $overlapEnd = $leave->end_date->lessThan($period->end_date)
                ? $leave->end_date
                : $period->end_date;

            $days = collect(CarbonPeriod::create($overlapStart, $overlapEnd))
                ->filter(fn (CarbonInterface $date) => $workDateKeys->has($date->toDateString()))
                ->count();

            if ($leave->leaveType?->is_paid) {
                $paidLeaveDays += $days;
            } else {
                $unpaidLeaveDays += $days;
            }
        }

        return [$paidLeaveDays, $unpaidLeaveDays];
    }

    /**
     * @param  Collection<int, AttendanceRecord>  $attendanceRecords
     */
    private function calculateOvertimePay(Collection $attendanceRecords, float $dailyRate, CompanySetting $settings): float
    {
        $hoursPerDay = max(1, (float) ($settings->standard_work_hours_per_day
            ?: config('attendance.default_work_hours_per_day', 8)));
        $weekdayMultiplier = max(1, (float) ($settings->overtime_weekday_multiplier
            ?: config('attendance.overtime_multiplier', 1.5)));
        $weekendMultiplier = max(1, (float) ($settings->overtime_weekend_multiplier ?: 2));
        $standardMinutes = (int) round($hoursPerDay * 60);
        $hourlyRate = $dailyRate > 0 ? $dailyRate / $hoursPerDay : 0.0;

        $total = 0.0;

        foreach ($attendanceRecords as $record) {
            $overtimeMinutes = max(0, (int) ($record->worked_minutes ?? 0) - $standardMinutes);

            if ($overtimeMinutes <= 0) {
                continue;
            }

            $isWeekend = $record->work_date->isWeekend();
            $multiplier = $isWeekend ? $weekendMultiplier : $weekdayMultiplier;
            $total += ($overtimeMinutes / 60) * $hourlyRate * $multiplier;
        }

        return round($total, 2);
    }

    /**
     * @param  Collection<int, AttendanceRecord>  $attendanceRecords
     */
    private function countLateOccurrences(Collection $attendanceRecords, ?WorkSchedule $schedule, CompanySetting $settings): int
    {
        if (! $schedule?->start_time) {
            return 0;
        }

        $grace = max(0, (int) ($settings->late_grace_minutes ?? 0));
        $startTime = Carbon::parse((string) $schedule->start_time)->format('H:i:s');

        return $attendanceRecords->filter(function (AttendanceRecord $record) use ($startTime, $grace): bool {
            if (! $record->clock_in_at) {
                return false;
            }

            $expected = $record->work_date->copy()->setTimeFromTimeString($startTime)->addMinutes($grace);

            return $record->clock_in_at->gt($expected);
        })->count();
    }

    /**
     * @return Collection<int, array{code: string, label: string, amount: float}>
     */
    private function bonusLinesForEmployee(PayrollPeriod $period, Employee $employee): Collection
    {
        return BonusRun::query()
            ->with(['items' => fn ($query) => $query->where('employee_id', $employee->id)])
            ->where('payroll_period_id', $period->id)
            ->where('status', BonusRunStatus::Applied)
            ->get()
            ->flatMap(function (BonusRun $run) {
                return $run->items
                    ->filter(fn ($item) => (float) $item->amount > 0)
                    ->map(fn ($item) => [
                        'code' => 'bonus_run_'.$run->id,
                        'label' => $item->label ?: ($run->name.' bonus'),
                        'amount' => round((float) $item->amount, 2),
                    ]);
            })
            ->values();
    }

    /**
     * @return Collection<int, ExpenseClaim>
     */
    private function approvedExpenseClaimsForEmployee(PayrollPeriod $period, Employee $employee): Collection
    {
        return ExpenseClaim::query()
            ->where('employee_id', $employee->id)
            ->whereIn('status', [ExpenseClaimStatus::Approved, ExpenseClaimStatus::Paid])
            ->where(function ($query) use ($period): void {
                $query->whereNull('payslip_id')
                    ->orWhereHas('payslip', fn ($payslip) => $payslip->where('payroll_period_id', $period->id));
            })
            ->whereBetween('expense_date', [$period->start_date, $period->end_date])
            ->orderBy('expense_date')
            ->get();
    }

    private function addLine(
        Payslip $payslip,
        PayslipLineType $type,
        string $code,
        string $label,
        float $amount,
        int $sortOrder,
        bool $isTaxable,
    ): void {
        $payslip->lines()->create([
            'type' => $type,
            'code' => $code,
            'label' => $label,
            'amount' => round($amount, 2),
            'is_manual' => false,
            'is_taxable' => $isTaxable,
            'sort_order' => $sortOrder,
        ]);
    }
}
