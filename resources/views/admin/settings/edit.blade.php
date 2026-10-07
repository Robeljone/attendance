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
