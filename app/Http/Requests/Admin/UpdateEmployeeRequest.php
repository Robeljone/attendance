<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Admin\Concerns\ValidatesEmployeeProfile;
use App\Models\Employee;
use Illuminate\Foundation\Http\FormRequest;

class UpdateEmployeeRequest extends FormRequest
{
    use ValidatesEmployeeProfile;

    public function authorize(): bool
    {
        return $this->user()?->canManageHr() ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Employee $employee */
        $employee = $this->route('employee');

        return $this->employeeProfileRules(
            employeeId: $employee->id,
            userId: $employee->user_id,
            requireStatus: true,
        );
    }
}
