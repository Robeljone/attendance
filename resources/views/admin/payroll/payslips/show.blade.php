@php
    $isDraft = $payrollPeriod->isDraft();
    $earnings = $payslip->lines->where('type', App\Enums\PayslipLineType::Earning);
    $deductions = $payslip->lines->where('type', App\Enums\PayslipLineType::Deduction);
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-900">
                    {{ $payslip->employee?->user?->name ?? __('Payslip') }}
                </h2>
                <p class="ui-card-subtitle mt-1">
                    {{ $payrollPeriod->name }}
                    · {{ $payrollPeriod->start_date?->format('M j, Y') }} – {{ $payrollPeriod->end_date?->format('M j, Y') }}
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('admin.payroll.show', $payrollPeriod) }}">
                    <x-secondary-button type="button">{{ __('Back to period') }}</x-secondary-button>
                </a>
                <a href="{{ route('admin.payroll.payslips.print', [$payrollPeriod, $payslip]) }}" target="_blank">
                    <x-primary-button type="button">{{ __('Print / PDF') }}</x-primary-button>
                </a>
            </div>
        </div>
    </x-slot>

    <x-ui.page>
        <x-ui.flash />

        @error('adjustment')
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $message }}</div>
        @enderror

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">
                <x-ui.card>
                    <h3 class="ui-card-title">{{ __('Attendance summary') }}</h3>
                    <dl class="mt-4 grid grid-cols-2 gap-4 text-sm sm:grid-cols-4">
                        <div>
                            <dt class="text-gray-500">{{ __('Present') }}</dt>
                            <dd class="mt-1 font-medium text-gray-900">{{ $payslip->present_days }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">{{ __('Absent') }}</dt>
                            <dd class="mt-1 font-medium text-gray-900">{{ $payslip->absent_days }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">{{ __('Leave') }}</dt>
                            <dd class="mt-1 font-medium text-gray-900">{{ $payslip->leave_days }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">{{ __('Net pay') }}</dt>
                            <dd class="mt-1 text-lg font-semibold text-gray-900">{{ number_format((float) $payslip->net_pay, 2) }}</dd>
                        </div>
                    </dl>
                </x-ui.card>

                <x-ui.card>
                    <h3 class="ui-card-title">{{ __('Earnings') }}</h3>
                    <ul class="mt-4 divide-y divide-gray-100 text-sm">
                        @forelse ($earnings as $line)
                            <li class="flex items-center justify-between gap-4 py-3">
                                <div>
                                    <p class="font-medium text-gray-900">{{ $line->label }}</p>
                                    @if ($line->is_manual)
                                        <p class="text-xs text-indigo-600">{{ __('Manual adjustment') }}</p>
                                    @endif
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="font-medium text-gray-900">{{ number_format((float) $line->amount, 2) }}</span>
                                    @if ($isDraft && $line->is_manual)
                                        <form method="POST" action="{{ route('admin.payroll.payslips.adjustments.destroy', [$payrollPeriod, $payslip, $line]) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs font-medium text-red-600 hover:text-red-700">{{ __('Remove') }}</button>
                                        </form>
                                    @endif
                                </div>
                            </li>
                        @empty
                            <li class="py-3 text-gray-500">{{ __('No earnings.') }}</li>
                        @endforelse
                    </ul>
                </x-ui.card>

                <x-ui.card>
                    <h3 class="ui-card-title">{{ __('Deductions') }}</h3>
                    <ul class="mt-4 divide-y divide-gray-100 text-sm">
                        @forelse ($deductions as $line)
                            <li class="flex items-center justify-between gap-4 py-3">
                                <div>
                                    <p class="font-medium text-gray-900">{{ $line->label }}</p>
                                    @if ($line->is_manual)
                                        <p class="text-xs text-indigo-600">{{ __('Manual adjustment') }}</p>
                                    @endif
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="font-medium text-gray-900">{{ number_format((float) $line->amount, 2) }}</span>
                                    @if ($isDraft && $line->is_manual)
                                        <form method="POST" action="{{ route('admin.payroll.payslips.adjustments.destroy', [$payrollPeriod, $payslip, $line]) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs font-medium text-red-600 hover:text-red-700">{{ __('Remove') }}</button>
                                        </form>
                                    @endif
                                </div>
                            </li>
                        @empty
                            <li class="py-3 text-gray-500">{{ __('No deductions.') }}</li>
                        @endforelse
                    </ul>
                </x-ui.card>
            </div>

            <div class="space-y-6">
                @if ($isDraft)
                    <x-ui.card>
                        <h3 class="ui-card-title">{{ __('Add adjustment') }}</h3>
                        <p class="ui-card-subtitle mt-1">{{ __('Add a bonus or one-off deduction before finalizing.') }}</p>

                        <form method="POST" action="{{ route('admin.payroll.payslips.adjustments.store', [$payrollPeriod, $payslip]) }}" class="mt-4 space-y-4">
                            @csrf
                            <div>
                                <x-input-label for="adjustment-type" :value="__('Type')" />
                                <x-ui.select id="adjustment-type" name="type" required>
                                    <option value="earning" @selected(old('type') === 'earning')>{{ __('Bonus / earning') }}</option>
                                    <option value="deduction" @selected(old('type') === 'deduction')>{{ __('Deduction') }}</option>
                                </x-ui.select>
                                <x-input-error class="mt-2" :messages="$errors->get('type')" />
                            </div>
                            <div>
                                <x-input-label for="adjustment-label" :value="__('Label')" />
                                <x-text-input id="adjustment-label" name="label" type="text" class="mt-1 block w-full" :value="old('label')" placeholder="{{ __('e.g. Performance bonus')" required />
                                <x-input-error class="mt-2" :messages="$errors->get('label')" />
                            </div>
                            <div>
                                <x-input-label for="adjustment-amount" :value="__('Amount')" />
                                <x-text-input id="adjustment-amount" name="amount" type="number" step="0.01" min="0.01" class="mt-1 block w-full" :value="old('amount')" required />
                                <x-input-error class="mt-2" :messages="$errors->get('amount')" />
                            </div>
                            <x-primary-button type="submit">{{ __('Add adjustment') }}</x-primary-button>
                        </form>
                    </x-ui.card>
                @else
                    <x-ui.card>
                        <h3 class="ui-card-title">{{ __('Finalized') }}</h3>
                        <p class="ui-card-subtitle mt-1">{{ __('This payroll is locked. Adjustments are disabled.') }}</p>
                    </x-ui.card>
                @endif
            </div>
        </div>
    </x-ui.page>
</x-app-layout>
