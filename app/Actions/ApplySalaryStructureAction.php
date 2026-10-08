<?php

namespace App\Actions;

use App\Models\Employee;
use App\Models\SalaryStructure;

class ApplySalaryStructureAction
{
    public function handle(Employee $employee, SalaryStructure $structure): Employee
    {
        $structure->loadMissing('payComponents');

        $sync = [];

        foreach ($structure->payComponents as $component) {
            if (! (bool) ($component->pivot->is_enabled ?? true)) {
                continue;
            }

            $amount = (float) ($component->default_amount ?? $component->pivot->amount ?? 0);

            if ($amount < 0) {
                continue;
            }

            $sync[$component->id] = [
                'amount' => $amount,
                'is_enabled' => true,
            ];
        }

        $employee->payComponents()->sync($sync);

        $assigned = $employee->payComponents()->get()->keyBy('code');

        $employee->update([
            'salary_structure_id' => $structure->id,
            'housing_allowance' => (float) ($assigned->get('housing_allowance')?->pivot->amount ?? 0),
            'transport_allowance' => (float) ($assigned->get('transport_allowance')?->pivot->amount ?? 0),
        ]);

        return $employee->refresh()->load(['payComponents', 'salaryStructure']);
    }
}
