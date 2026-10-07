<?php

namespace App\Http\Requests\Admin;

use App\Enums\EducationLevel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmployeeEducationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canManageHr() ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'institution' => ['required', 'string', 'max:255'],
            'level' => ['required', Rule::enum(EducationLevel::class)],
            'field_of_study' => ['nullable', 'string', 'max:255'],
            'degree_title' => ['nullable', 'string', 'max:255'],
            'start_year' => ['nullable', 'integer', 'min:1950', 'max:'.(now()->year + 1)],
            'end_year' => ['nullable', 'integer', 'min:1950', 'max:'.(now()->year + 10), 'gte:start_year'],
            'grade' => ['nullable', 'string', 'max:50'],
            'is_highest' => ['sometimes', 'boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
