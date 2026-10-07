<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-900">{{ __('Payslip') }}</h2>
            <a href="{{ route('portal.payslips.index') }}"><x-secondary-button type="button">{{ __('Back') }}</x-secondary-button></a>
        </div>
    </x-slot>

    <x-ui.page>
        <x-ui.card>
            <div>
                <p class="text-sm text-gray-500">{{ __('Pay period') }}</p>
                <p class="ui-card-title mt-0.5">{{ $payslip->payrollPeriod?->name ?? '—' }}</p>
                <p class="ui-card-subtitle mt-1">
                    {{ $payslip->payrollPeriod?->start_date?->format('M j, Y') }} – {{ $payslip->payrollPeriod?->end_date?->format('M j, Y') }}
                </p>
            </div>

            <dl class="mt-6 grid grid-cols-2 gap-4 text-sm">
                <div>
                    <dt class="text-gray-500">{{ __('Base salary') }}</dt>
                    <dd class="mt-1 font-medium text-gray-900">{{ number_format((float) ($payslip->base_salary ?? 0), 2) }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">{{ __('Present days') }}</dt>
                    <dd class="mt-1 text-gray-900">{{ $payslip->present_days ?? 0 }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">{{ __('Absent days') }}</dt>
                    <dd class="mt-1 text-gray-900">{{ $payslip->absent_days ?? 0 }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">{{ __('Leave days') }}</dt>
                    <dd class="mt-1 text-gray-900">{{ $payslip->leave_days ?? 0 }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">{{ __('Overtime') }}</dt>
                    <dd class="mt-1 text-gray-900">{{ number_format((float) ($payslip->overtime_pay ?? 0), 2) }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">{{ __('Bonuses') }}</dt>
                    <dd class="mt-1 text-gray-900">{{ number_format((float) ($payslip->bonuses ?? 0), 2) }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">{{ __('Deductions') }}</dt>
                    <dd class="mt-1 text-gray-900">{{ number_format((float) ($payslip->deductions ?? 0), 2) }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">{{ __('Net pay') }}</dt>
                    <dd class="mt-1 text-lg font-semibold text-gray-900">{{ number_format((float) ($payslip->net_pay ?? 0), 2) }}</dd>
                </div>
            </dl>

            @if (! empty($payslip->breakdown))
                <div class="mt-6 border-t border-gray-100 pt-6">
                    <h3 class="ui-card-title">{{ __('Breakdown') }}</h3>
                    <ul class="mt-2 space-y-1 text-sm text-gray-600">
                        @foreach ((array) $payslip->breakdown as $label => $amount)
                            <li class="flex justify-between">
                                <span>{{ is_string($label) ? $label : ($amount['label'] ?? '') }}</span>
                                <span>{{ is_array($amount) ? number_format((float) ($amount['value'] ?? 0), 2) : number_format((float) $amount, 2) }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </x-ui.card>
    </x-ui.page>
</x-app-layout>
