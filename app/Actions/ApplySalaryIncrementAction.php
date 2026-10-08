<?php

namespace App\Actions;

use App\Enums\SalaryIncrementStatus;
use App\Enums\SalaryIncrementType;
use App\Models\SalaryIncrement;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ApplySalaryIncrementAction
{
    public function handle(SalaryIncrement $increment, User $actor): SalaryIncrement
    {
        if (! $increment->isPending()) {
            throw new InvalidArgumentException('Only pending salary increments can be applied.');
        }

        $increment->loadMissing('employee');

        $employee = $increment->employee;

        if (! $employee) {
            throw new InvalidArgumentException('Employee not found for this increment.');
        }

        return DB::transaction(function () use ($increment, $actor, $employee) {
            $previous = round((float) $employee->base_salary, 2);

            $newSalary = match ($increment->type) {
                SalaryIncrementType::Percent => round($previous * (1 + ((float) $increment->amount / 100)), 2),
                default => round($previous + (float) $increment->amount, 2),
            };

            $employee->update(['base_salary' => max(0, $newSalary)]);

            $increment->update([
                'status' => SalaryIncrementStatus::Applied,
                'previous_base_salary' => $previous,
                'new_base_salary' => max(0, $newSalary),
                'applied_by' => $actor->id,
                'applied_at' => now(),
            ]);

            return $increment->refresh()->load(['employee.user']);
        });
    }
}
