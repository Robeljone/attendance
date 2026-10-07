@php
    $status = $payrollPeriod->status;
    $statusTone = match ($status?->value) {
        'draft' => 'gray',
        'pending_approval' => 'indigo',
        'finalized' => 'green',
        'paid' => 'green',
        default => 'gray',
    };
    $summary = $summary ?? $payrollPeriod->summary();
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <div class="flex flex-wrap items-center gap-3">
                    <h2 class="text-xl font-semibold leading-tight text-gray-900">{{ $payrollPeriod->name }}</h2>
                    <x-ui.badge :tone="$statusTone">{{ $status?->label() ?? '—' }}</x-ui.badge>
                </div>
                <p class="ui-card-subtitle mt-1">
                    {{ $payrollPeriod->start_date?->format('M j, Y') }} – {{ $payrollPeriod->end_date?->format('M j, Y') }}
                    · {{ $summary['headcount'] }} {{ __('employees') }}
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('admin.payroll.index') }}">
                    <x-secondary-button type="button">{{ __('Back to payroll') }}</x-secondary-button>
                </a>
                @if ($payrollPeriod->isDraft())
                    <form method="POST" action="{{ route('admin.payroll.regenerate', $payrollPeriod) }}">
                        @csrf
                        <x-secondary-button type="submit">{{ __('Regenerate') }}</x-secondary-button>
                    </form>
                    <form method="POST" action="{{ route('admin.payroll.submit', $payrollPeriod) }}">
                        @csrf
                        <x-primary-button type="submit">{{ __('Submit for approval') }}</x-primary-button>
                    </form>
                    <form method="POST" action="{{ route('admin.payroll.finalize', $payrollPeriod) }}" onsubmit="return confirm(@js(__('Finalize without approval workflow?')))">
                        @csrf
                        <x-secondary-button type="submit">{{ __('Finalize now') }}</x-secondary-button>
                    </form>
                @endif
                @if ($payrollPeriod->isPendingApproval())
                    <form method="POST" action="{{ route('admin.payroll.finalize', $payrollPeriod) }}" onsubmit="return confirm(@js(__('Approve and finalize this payroll?')))">
                        @csrf
                        <x-primary-button type="submit">{{ __('Approve & finalize') }}</x-primary-button>
                    </form>
                @endif
                @if ($payrollPeriod->isFinalized())
                    <a href="{{ route('admin.payroll.export-bank-csv', $payrollPeriod) }}">
                        <x-secondary-button type="button">{{ __('Export bank CSV') }}</x-secondary-button>
                    </a>
                    <form method="POST" action="{{ route('admin.payroll.mark-paid', $payrollPeriod) }}">
                        @csrf
                        <x-primary-button type="submit">{{ __('Mark as paid') }}</x-primary-button>
                    </form>
                @endif
                @if ($payrollPeriod->isPaid())
                    <a href="{{ route('admin.payroll.export-bank-csv', $payrollPeriod) }}">
                        <x-secondary-button type="button">{{ __('Export bank CSV') }}</x-secondary-button>
                    </a>
                @endif
            </div>
        </div>
    </x-slot>

    <x-ui.page>
        <x-ui.flash />

        @error('payroll')
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $message }}</div>
        @enderror

        <div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
            <x-ui.card>
                <p class="text-sm text-gray-500">{{ __('Headcount') }}</p>
                <p class="mt-1 text-2xl font-semibold text-gray-900">{{ $summary['headcount'] }}</p>
            </x-ui.card>
            <x-ui.card>
                <p class="text-sm text-gray-500">{{ __('Gross') }}</p>
                <p class="mt-1 text-2xl font-semibold text-gray-900">{{ number_format($summary['gross'], 2) }}</p>
            </x-ui.card>
            <x-ui.card>
                <p class="text-sm text-gray-500">{{ __('Deductions') }}</p>
                <p class="mt-1 text-2xl font-semibold text-gray-900">{{ number_format($summary['deductions'], 2) }}</p>
            </x-ui.card>
            <x-ui.card>
                <p class="text-sm text-gray-500">{{ __('Overtime') }}</p>
                <p class="mt-1 text-2xl font-semibold text-gray-900">{{ number_format($summary['overtime'], 2) }}</p>
            </x-ui.card>
            <x-ui.card>
                <p class="text-sm text-gray-500">{{ __('Net pay') }}</p>
                <p class="mt-1 text-2xl font-semibold text-gray-900">{{ number_format($summary['net'], 2) }}</p>
            </x-ui.card>
        </div>

        @if ($payrollPeriod->isDraft())
            <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                {{ __('Draft run: adjust payslips, then submit for approval or finalize.') }}
            </div>
        @elseif ($payrollPeriod->isPendingApproval())
            <div class="mb-4 rounded-lg border border-indigo-200 bg-indigo-50 px-4 py-3 text-sm text-indigo-800">
                {{ __('Waiting for approval. Regenerating is locked until this is finalized or returned to draft (regenerate only works on drafts).') }}
            </div>
        @endif

        <x-ui.card flush>
            <div class="ui-table-wrap">
                <table class="ui-table">
                    <thead class="ui-thead">
                        <tr>
                            <th class="ui-th">{{ __('Employee') }}</th>
                            <th class="ui-th">{{ __('Present') }}</th>
                            <th class="ui-th">{{ __('Absent') }}</th>
                            <th class="ui-th">{{ __('Leave') }}</th>
                            <th class="ui-th">{{ __('Gross') }}</th>
                            <th class="ui-th">{{ __('Deductions') }}</th>
                            <th class="ui-th">{{ __('Net pay') }}</th>
                            <th class="ui-th-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="ui-tbody">
                        @forelse ($payrollPeriod->payslips as $payslip)
                            <tr class="ui-tr">
                                <td class="ui-td-strong">{{ $payslip->employee?->user?->name ?? $payslip->employee?->employee_number ?? '—' }}</td>
                                <td class="ui-td">{{ $payslip->present_days ?? 0 }}</td>
                                <td class="ui-td">{{ $payslip->absent_days ?? 0 }}</td>
                                <td class="ui-td">{{ $payslip->leave_days ?? 0 }}</td>
                                <td class="ui-td">{{ number_format((float) $payslip->grossPay(), 2) }}</td>
                                <td class="ui-td">{{ number_format((float) ($payslip->deductions ?? 0), 2) }}</td>
                                <td class="ui-td-strong">{{ number_format((float) ($payslip->net_pay ?? 0), 2) }}</td>
                                <td class="ui-td-right">
                                    <div class="ui-actions">
                                        <x-ui.table-action :href="route('admin.payroll.payslips.show', [$payrollPeriod, $payslip])" icon="document">
                                            {{ __('Open') }}
                                        </x-ui.table-action>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr class="ui-tr">
                                <td colspan="8" class="ui-td-empty">{{ __('No payslips in this period.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.card>
    </x-ui.page>
</x-app-layout>
