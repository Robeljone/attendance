<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateBrandingRequest;
use App\Models\CompanySetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class BrandingController extends Controller
{
    public function edit(): View
    {
        return view('admin.branding.edit', [
            'settings' => CompanySetting::current(),
        ]);
    }

    public function update(UpdateBrandingRequest $request): RedirectResponse
    {
        $settings = CompanySetting::current();
        $validated = $request->validated();

        $data = [
            'company_name' => $validated['company_name'],
            'tagline' => $validated['tagline'] ?? null,
            'primary_color' => $validated['primary_color'] ?? null,
            'support_email' => $validated['support_email'] ?? null,
        ];

        if ($request->boolean('remove_logo') && $settings->logo_path) {
            Storage::disk('public')->delete($settings->logo_path);
            $data['logo_path'] = null;
        }

        if ($request->boolean('remove_favicon') && $settings->favicon_path) {
            Storage::disk('public')->delete($settings->favicon_path);
            $data['favicon_path'] = null;
        }

        if ($request->hasFile('logo')) {
            if ($settings->logo_path) {
                Storage::disk('public')->delete($settings->logo_path);
            }

            $data['logo_path'] = $request->file('logo')->store('branding', 'public');
        }

        if ($request->hasFile('favicon')) {
            if ($settings->favicon_path) {
                Storage::disk('public')->delete($settings->favicon_path);
            }

            $data['favicon_path'] = $request->file('favicon')->store('branding', 'public');
        }

        $settings->update($data);

        return back()->with('success', 'Branding settings saved.');
    }
}
