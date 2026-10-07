@php
    $settings = $settings ?? \App\Models\CompanySetting::current();
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-900">{{ __('Branding') }}</h2>
    </x-slot>

    <x-ui.page width="md">
        <x-ui.flash />

        <x-ui.card>
            <div class="mb-6">
                <h3 class="ui-card-title">{{ __('Company branding') }}</h3>
                <p class="ui-card-subtitle">{{ __('Manage the company name, logo, favicon, and brand color shown across the app.') }}</p>
            </div>

            <form method="POST" action="{{ route('admin.branding.update') }}" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PUT')

                <div>
                    <x-input-label for="company_name" :value="__('Company name')" />
                    <x-text-input id="company_name" name="company_name" type="text" class="mt-1 block w-full" :value="old('company_name', $settings->company_name)" required />
                    <x-input-error class="mt-2" :messages="$errors->get('company_name')" />
                </div>

                <div>
                    <x-input-label for="tagline" :value="__('Tagline')" />
                    <x-text-input id="tagline" name="tagline" type="text" class="mt-1 block w-full" :value="old('tagline', $settings->tagline)" placeholder="{{ __('Attendance & HR') }}" />
                    <x-input-error class="mt-2" :messages="$errors->get('tagline')" />
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <x-input-label for="support_email" :value="__('Support email')" />
                        <x-text-input id="support_email" name="support_email" type="email" class="mt-1 block w-full" :value="old('support_email', $settings->support_email)" />
                        <x-input-error class="mt-2" :messages="$errors->get('support_email')" />
                    </div>
                    <div>
                        <x-input-label for="primary_color" :value="__('Primary color')" />
                        <div class="mt-1 flex items-center gap-3">
                            <input
                                id="primary_color_picker"
                                type="color"
                                class="h-10 w-12 cursor-pointer rounded border border-gray-300 bg-white p-1"
                                value="{{ old('primary_color', $settings->primary_color ?: '#4F46E5') }}"
                                oninput="document.getElementById('primary_color').value = this.value.toUpperCase()"
                            >
                            <x-text-input
                                id="primary_color"
                                name="primary_color"
                                type="text"
                                class="block w-full font-mono uppercase"
                                :value="old('primary_color', $settings->primary_color)"
                                placeholder="#4F46E5"
                                maxlength="7"
                                oninput="if (/^#[0-9A-Fa-f]{6}$/.test(this.value)) document.getElementById('primary_color_picker').value = this.value"
                            />
                        </div>
                        <x-input-error class="mt-2" :messages="$errors->get('primary_color')" />
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <x-input-label for="logo" :value="__('Logo')" />
                        <div class="mt-2 flex items-center gap-4 rounded-xl border border-gray-200 bg-gray-50 p-4">
                            @if ($settings->logoUrl())
                                <img src="{{ $settings->logoUrl() }}" alt="{{ __('Company logo') }}" class="h-14 w-auto max-w-[10rem] object-contain">
                            @else
                                <div class="flex h-14 w-14 items-center justify-center rounded-lg bg-white text-xs text-gray-400 ring-1 ring-gray-200">
                                    {{ __('None') }}
                                </div>
                            @endif
                            <div class="min-w-0 flex-1 space-y-2">
                                <input id="logo" name="logo" type="file" accept=".jpg,.jpeg,.png,.webp,.svg" class="block w-full text-sm text-gray-600 file:mr-3 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-3 file:py-2 file:text-sm file:font-medium file:text-indigo-700 hover:file:bg-indigo-100" />
                                @if ($settings->logo_path)
                                    <label class="inline-flex items-center gap-2 text-sm text-gray-600">
                                        <input type="checkbox" name="remove_logo" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                        {{ __('Remove current logo') }}
                                    </label>
                                @endif
                            </div>
                        </div>
                        <p class="mt-1 text-sm text-gray-500">{{ __('PNG, JPG, WEBP, or SVG. Max 2MB.') }}</p>
                        <x-input-error class="mt-2" :messages="$errors->get('logo')" />
                    </div>

                    <div>
                        <x-input-label for="favicon" :value="__('Favicon')" />
                        <div class="mt-2 flex items-center gap-4 rounded-xl border border-gray-200 bg-gray-50 p-4">
                            @if ($settings->faviconUrl())
                                <img src="{{ $settings->faviconUrl() }}" alt="{{ __('Favicon') }}" class="h-10 w-10 object-contain">
                            @else
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-white text-[10px] text-gray-400 ring-1 ring-gray-200">
                                    {{ __('None') }}
                                </div>
                            @endif
                            <div class="min-w-0 flex-1 space-y-2">
                                <input id="favicon" name="favicon" type="file" accept=".jpg,.jpeg,.png,.webp,.ico,.svg" class="block w-full text-sm text-gray-600 file:mr-3 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-3 file:py-2 file:text-sm file:font-medium file:text-indigo-700 hover:file:bg-indigo-100" />
                                @if ($settings->favicon_path)
                                    <label class="inline-flex items-center gap-2 text-sm text-gray-600">
                                        <input type="checkbox" name="remove_favicon" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                        {{ __('Remove current favicon') }}
                                    </label>
                                @endif
                            </div>
                        </div>
                        <p class="mt-1 text-sm text-gray-500">{{ __('ICO, PNG, JPG, WEBP, or SVG. Max 512KB.') }}</p>
                        <x-input-error class="mt-2" :messages="$errors->get('favicon')" />
                    </div>
                </div>

                <div class="flex items-center gap-3 border-t border-gray-100 pt-4">
                    <x-primary-button>{{ __('Save branding') }}</x-primary-button>
                </div>
            </form>
        </x-ui.card>
    </x-ui.page>
</x-app-layout>
