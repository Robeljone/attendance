<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Admin\Concerns\ValidatesEmployeeProfile;
use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeRequest extends FormRequest
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
        return $this->employeeProfileRules(requirePassword: false);
    }
}
