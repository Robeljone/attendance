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
            'allowed_ip_cidrs' => $cidrs,
            'enforce_company_network' => $request->boolean('enforce_company_network'),
        ]);

        return back()->with('success', 'Company settings saved. Attendance will require company network IPs when enforcement is enabled.');
    }
}
