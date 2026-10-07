<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-900">{{ $payrollPeriod->name ?? __('Payroll period') }}</h2>
                <p class="ui-card-subtitle mt-1">
                    {{ $payrollPeriod->start_date?->format('M j, Y') }} – {{ $payrollPeriod->end_date?->format('M j, Y') }}
                </p>
            </div>
            <a href="{{ route('admin.payroll.index') }}"><x-secondary-button type="button">{{ __('Back to payroll') }}</x-secondary-button></a>
        </div>
    </x-slot>

    <x-ui.page>
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
                        </tr>
                    </thead>
                    <tbody class="ui-tbody">
                        @forelse ($payslips ?? $payrollPeriod->payslips ?? [] as $payslip)
                            <tr class="ui-tr">
                                <td class="ui-td-strong">{{ $payslip->employee?->user?->name ?? $payslip->employee?->employee_number ?? '—' }}</td>
                                <td class="ui-td">{{ $payslip->present_days ?? 0 }}</td>
                                <td class="ui-td">{{ $payslip->absent_days ?? 0 }}</td>
                                <td class="ui-td">{{ $payslip->leave_days ?? 0 }}</td>
                                <td class="ui-td">{{ number_format((float) ($payslip->base_salary ?? 0) + (float) ($payslip->bonuses ?? 0) + (float) ($payslip->overtime_pay ?? 0), 2) }}</td>
                                <td class="ui-td">{{ number_format((float) ($payslip->deductions ?? 0), 2) }}</td>
                                <td class="ui-td-strong">{{ number_format((float) ($payslip->net_pay ?? 0), 2) }}</td>
                            </tr>
                        @empty
                            <tr class="ui-tr">
                                <td colspan="7" class="ui-td-empty">{{ __('No payslips in this period.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.card>
    </x-ui.page>
</x-app-layout>
