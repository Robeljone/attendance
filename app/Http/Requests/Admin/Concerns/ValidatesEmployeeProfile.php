<?php

namespace App\Http\Requests\Admin\Concerns;

use App\Enums\EmploymentStatus;
use App\Enums\Gender;
use App\Enums\MaritalStatus;
use App\Enums\UserRole;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

trait ValidatesEmployeeProfile
{
    /**
     * @return array<string, mixed>
     */
    protected function employeeProfileRules(?int $employeeId = null, ?int $userId = null, bool $requirePassword = false, bool $requireStatus = false): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'password' => [$requirePassword ? 'required' : 'nullable', Password::defaults()],
            'role' => ['required', Rule::enum(UserRole::class)],
            'employee_number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('employees', 'employee_number')->ignore($employeeId),
            ],
            'department_id' => ['nullable', 'exists:departments,id'],
            'work_schedule_id' => ['nullable', 'exists:work_schedules,id'],
            'phone' => ['nullable', 'string', 'max:50'],
            'position' => ['nullable', 'string', 'max:100'],
            'hire_date' => ['nullable', 'date'],
            'termination_date' => ['nullable', 'date', 'after_or_equal:hire_date'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'base_salary' => ['nullable', 'numeric', 'min:0'],
            'housing_allowance' => ['nullable', 'numeric', 'min:0'],
            'transport_allowance' => ['nullable', 'numeric', 'min:0'],
            'pay_components' => ['nullable', 'array'],
            'pay_components.*.amount' => ['nullable', 'numeric', 'min:0'],
            'pay_components.*.enabled' => ['nullable', 'boolean'],
            'address' => ['nullable', 'string', 'max:255'],
            'bank_account' => ['nullable', 'string', 'max:100'],
            'status' => [$requireStatus ? 'required' : 'nullable', Rule::enum(EmploymentStatus::class)],
            'is_active' => ['nullable', 'boolean'],
            'gender' => ['nullable', Rule::enum(Gender::class)],
            'marital_status' => ['nullable', Rule::enum(MaritalStatus::class)],
            'nationality' => ['nullable', 'string', 'max:100'],
            'national_id' => ['nullable', 'string', 'max:100'],
            'tax_id' => ['nullable', 'string', 'max:100'],
            'personal_email' => ['nullable', 'email', 'max:255'],
            'blood_group' => ['nullable', 'string', 'max:10'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:50'],
            'emergency_contact_relationship' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_photo' => ['sometimes', 'boolean'],
        ];
    }
}
