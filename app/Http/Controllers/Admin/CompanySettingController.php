<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompanySetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CompanySettingController extends Controller
{
    public function edit(): View
    {
        return view('admin.settings.edit', [
            'settings' => CompanySetting::current(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'timezone' => ['required', 'timezone'],
            'currency' => ['required', 'string', 'size:3'],
            'income_tax_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'pension_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'standard_work_hours_per_day' => ['nullable', 'numeric', 'min:1', 'max:24'],
            'overtime_weekday_multiplier' => ['nullable', 'numeric', 'min:1', 'max:10'],
            'overtime_weekend_multiplier' => ['nullable', 'numeric', 'min:1', 'max:10'],
            'late_grace_minutes' => ['nullable', 'integer', 'min:0', 'max:240'],
            'late_penalty_per_occurrence' => ['nullable', 'numeric', 'min:0'],
            'deduct_unexcused_absence' => ['nullable', 'boolean'],
            'allowed_ip_cidrs' => ['nullable', 'string'],
            'enforce_company_network' => ['nullable', 'boolean'],
        ]);

        $cidrs = collect(preg_split('/[\s,]+/', (string) ($validated['allowed_ip_cidrs'] ?? ''), -1, PREG_SPLIT_NO_EMPTY))
            ->map(fn (string $cidr) => trim($cidr))
            ->filter()
            ->values()
            ->all();

        $settings = CompanySetting::current();
        $settings->update([
            'company_name' => $validated['company_name'],
            'timezone' => $validated['timezone'],
            'currency' => strtoupper($validated['currency']),
            'income_tax_percent' => $validated['income_tax_percent'] ?? 0,
            'pension_percent' => $validated['pension_percent'] ?? 0,
            'standard_work_hours_per_day' => $validated['standard_work_hours_per_day'] ?? 8,
            'overtime_weekday_multiplier' => $validated['overtime_weekday_multiplier'] ?? 1.5,
            'overtime_weekend_multiplier' => $validated['overtime_weekend_multiplier'] ?? 2,
            'late_grace_minutes' => $validated['late_grace_minutes'] ?? 15,
            'late_penalty_per_occurrence' => $validated['late_penalty_per_occurrence'] ?? 0,
            'deduct_unexcused_absence' => $request->boolean('deduct_unexcused_absence'),
            'allowed_ip_cidrs' => $cidrs,
            'enforce_company_network' => $request->boolean('enforce_company_network'),
        ]);

        return back()->with('success', 'Company settings saved. Attendance will require company network IPs when enforcement is enabled.');
    }
}
