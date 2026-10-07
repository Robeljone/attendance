<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBrandingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canManageBranding() ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'company_name' => ['required', 'string', 'max:255'],
            'tagline' => ['nullable', 'string', 'max:255'],
            'support_email' => ['nullable', 'email', 'max:255'],
            'primary_color' => ['nullable', 'string', 'regex:/^#([A-Fa-f0-9]{6})$/'],
            'logo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],
            'favicon' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,ico,svg', 'max:512'],
            'remove_logo' => ['sometimes', 'boolean'],
            'remove_favicon' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'primary_color.regex' => 'The primary color must be a valid hex color like #4F46E5.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('primary_color')) {
            $this->merge([
                'primary_color' => strtoupper((string) $this->input('primary_color')),
            ]);
        }
    }
}
