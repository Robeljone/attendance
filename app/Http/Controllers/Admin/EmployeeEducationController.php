<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreEmployeeEducationRequest;
use App\Http\Requests\Admin\UpdateEmployeeEducationRequest;
use App\Models\Employee;
use App\Models\EmployeeEducation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class EmployeeEducationController extends Controller
{
    public function store(StoreEmployeeEducationRequest $request, Employee $employee): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($employee, $validated): void {
            if (! empty($validated['is_highest'])) {
                $employee->educations()->update(['is_highest' => false]);
            }

            $employee->educations()->create([
                ...$validated,
                'is_highest' => (bool) ($validated['is_highest'] ?? false),
            ]);
        });

        return redirect()
            ->route('admin.employees.show', $employee)
            ->with('success', 'Education record added.');
    }

    public function update(UpdateEmployeeEducationRequest $request, Employee $employee, EmployeeEducation $education): RedirectResponse
    {
        abort_unless($education->employee_id === $employee->id, 404);

        $validated = $request->validated();

        DB::transaction(function () use ($employee, $education, $validated): void {
            if (! empty($validated['is_highest'])) {
                $employee->educations()
                    ->whereKeyNot($education->id)
                    ->update(['is_highest' => false]);
            }

            $education->update([
                ...$validated,
                'is_highest' => (bool) ($validated['is_highest'] ?? false),
            ]);
        });

        return redirect()
            ->route('admin.employees.show', $employee)
            ->with('success', 'Education record updated.');
    }

    public function destroy(Employee $employee, EmployeeEducation $education): RedirectResponse
    {
        abort_unless($education->employee_id === $employee->id, 404);

        $education->delete();

        return redirect()
            ->route('admin.employees.show', $employee)
            ->with('success', 'Education record removed.');
    }
}
