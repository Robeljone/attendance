<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-900">{{ __('Company settings') }}</h2>
    </x-slot>

    <x-ui.page width="md">
        <x-ui.flash />

        <x-ui.card>
            @php
                $settings = $settings ?? \App\Models\CompanySetting::current();
                $cidrsText = old('allowed_ip_cidrs');
                if ($cidrsText === null) {
                    $cidrs = $settings->allowed_ip_cidrs ?? [];
                    $cidrsText = is_array($cidrs) ? implode("\n", $cidrs) : (string) $cidrs;
                }
            @endphp

            <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-6">
                @csrf
                @method('PUT')

                <div>
                    <x-input-label for="company_name" :value="__('Company name')" />
                    <x-text-input id="company_name" name="company_name" type="text" class="mt-1 block w-full" :value="old('company_name', $settings->company_name)" required />
                    <x-input-error class="mt-2" :messages="$errors->get('company_name')" />
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <x-input-label for="currency" :value="__('Currency')" />
                        <x-text-input id="currency" name="currency" type="text" class="mt-1 block w-full" :value="old('currency', $settings->currency)" maxlength="3" required />
                        <x-input-error class="mt-2" :messages="$errors->get('currency')" />
                    </div>
                    <div>
                        <x-input-label for="timezone" :value="__('Timezone')" />
                        <x-text-input id="timezone" name="timezone" type="text" class="mt-1 block w-full" :value="old('timezone', $settings->timezone)" required />
                        <x-input-error class="mt-2" :messages="$errors->get('timezone')" />
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <x-input-label for="income_tax_percent" :value="__('Income tax %')" />
                        <x-text-input id="income_tax_percent" name="income_tax_percent" type="number" step="0.01" min="0" max="100" class="mt-1 block w-full" :value="old('income_tax_percent', $settings->income_tax_percent ?? 0)" />
                        <p class="mt-1 text-sm text-gray-500">{{ __('Applied to taxable earnings on each payslip.') }}</p>
                        <x-input-error class="mt-2" :messages="$errors->get('income_tax_percent')" />
                    </div>
                    <div>
                        <x-input-label for="pension_percent" :value="__('Pension %')" />
                        <x-text-input id="pension_percent" name="pension_percent" type="number" step="0.01" min="0" max="100" class="mt-1 block w-full" :value="old('pension_percent', $settings->pension_percent ?? 0)" />
                        <p class="mt-1 text-sm text-gray-500">{{ __('Employee pension contribution percent.') }}</p>
                        <x-input-error class="mt-2" :messages="$errors->get('pension_percent')" />
                    </div>
                </div>

                <div class="border-t border-gray-100 pt-5">
                    <h3 class="text-sm font-semibold text-gray-900">{{ __('Attendance pay rules') }}</h3>
                    <p class="mt-1 text-sm text-gray-500">{{ __('Used when generating payroll overtime, late penalties, and absence (LOP) deductions.') }}</p>
                    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <x-input-label for="standard_work_hours_per_day" :value="__('Standard work hours / day')" />
                            <x-text-input id="standard_work_hours_per_day" name="standard_work_hours_per_day" type="number" step="0.25" min="1" max="24" class="mt-1 block w-full" :value="old('standard_work_hours_per_day', $settings->standard_work_hours_per_day ?? 8)" />
                        </div>
                        <div>
                            <x-input-label for="late_grace_minutes" :value="__('Late grace (minutes)')" />
                            <x-text-input id="late_grace_minutes" name="late_grace_minutes" type="number" min="0" max="240" class="mt-1 block w-full" :value="old('late_grace_minutes', $settings->late_grace_minutes ?? 15)" />
                        </div>
                        <div>
                            <x-input-label for="overtime_weekday_multiplier" :value="__('Weekday OT multiplier')" />
                            <x-text-input id="overtime_weekday_multiplier" name="overtime_weekday_multiplier" type="number" step="0.1" min="1" max="10" class="mt-1 block w-full" :value="old('overtime_weekday_multiplier', $settings->overtime_weekday_multiplier ?? 1.5)" />
                        </div>
                        <div>
                            <x-input-label for="overtime_weekend_multiplier" :value="__('Weekend OT multiplier')" />
                            <x-text-input id="overtime_weekend_multiplier" name="overtime_weekend_multiplier" type="number" step="0.1" min="1" max="10" class="mt-1 block w-full" :value="old('overtime_weekend_multiplier', $settings->overtime_weekend_multiplier ?? 2)" />
                        </div>
                        <div>
                            <x-input-label for="late_penalty_per_occurrence" :value="__('Late penalty per occurrence')" />
                            <x-text-input id="late_penalty_per_occurrence" name="late_penalty_per_occurrence" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('late_penalty_per_occurrence', $settings->late_penalty_per_occurrence ?? 0)" />
                        </div>
                        <div class="flex items-end pb-2">
                            <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                <input id="deduct_unexcused_absence" name="deduct_unexcused_absence" type="checkbox" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" @checked(old('deduct_unexcused_absence', $settings->deduct_unexcused_absence ?? true)) />
                                {{ __('Deduct unexcused absence (LOP)') }}
                            </label>
                        </div>
                    </div>
                </div>

                <div class="flex items-center">
                    <input id="enforce_company_network" name="enforce_company_network" type="checkbox" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" @checked(old('enforce_company_network', $settings->enforce_company_network)) />
                    <x-input-label for="enforce_company_network" :value="__('Enforce company network for clock-in')" class="ms-2" />
                </div>

                <div>
                    <x-input-label for="allowed_ip_cidrs" :value="__('Allowed IP CIDRs')" />
                    <textarea id="allowed_ip_cidrs" name="allowed_ip_cidrs" rows="5" class="ui-textarea font-mono text-sm" placeholder="192.168.1.0/24&#10;10.0.0.0/8">{{ $cidrsText }}</textarea>
                    <p class="mt-1 text-sm text-gray-500">{{ __('One CIDR per line or comma-separated.') }}</p>
                    <x-input-error class="mt-2" :messages="$errors->get('allowed_ip_cidrs')" />
                </div>

                <x-primary-button>{{ __('Save settings') }}</x-primary-button>
            </form>
        </x-ui.card>
    </x-ui.page>
</x-app-layout>
