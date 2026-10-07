@php
    $earnings = $payslip->lines->where('type', App\Enums\PayslipLineType::Earning);
    $deductions = $payslip->lines->where('type', App\Enums\PayslipLineType::Deduction);
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-900">{{ __('Payslip') }}</h2>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('portal.payslips.index') }}">
                    <x-secondary-button type="button">{{ __('Back') }}</x-secondary-button>
                </a>
                <a href="{{ route('portal.payslips.print', $payslip) }}" target="_blank">
                    <x-primary-button type="button">{{ __('Print / PDF') }}</x-primary-button>
                </a>
            </div>
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
                    <dt class="text-gray-500">{{ __('Net pay') }}</dt>
                    <dd class="mt-1 text-lg font-semibold text-gray-900">{{ number_format((float) ($payslip->net_pay ?? 0), 2) }}</dd>
                </div>
            </dl>
        </x-ui.card>

        <div class="mt-6 grid gap-6 lg:grid-cols-2">
            <x-ui.card>
                <h3 class="ui-card-title">{{ __('Earnings') }}</h3>
                <ul class="mt-4 space-y-2 text-sm">
                    @foreach ($earnings as $line)
                        <li class="flex justify-between gap-4">
                            <span class="text-gray-600">{{ $line->label }}</span>
                            <span class="font-medium text-gray-900">{{ number_format((float) $line->amount, 2) }}</span>
                        </li>
                    @endforeach
                </ul>
            </x-ui.card>

            <x-ui.card>
                <h3 class="ui-card-title">{{ __('Deductions') }}</h3>
                <ul class="mt-4 space-y-2 text-sm">
                    @forelse ($deductions as $line)
                        <li class="flex justify-between gap-4">
                            <span class="text-gray-600">{{ $line->label }}</span>
                            <span class="font-medium text-gray-900">{{ number_format((float) $line->amount, 2) }}</span>
                        </li>
                    @empty
                        <li class="text-gray-500">{{ __('No deductions') }}</li>
                    @endforelse
                </ul>
            </x-ui.card>
        </div>
    </x-ui.page>
</x-app-layout>
