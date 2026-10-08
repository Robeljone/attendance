<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-900">{{ __('Reports') }}</h2>
            <a href="{{ route('admin.reports.audit') }}">
                <x-primary-button type="button">{{ __('Employee attendance audit') }}</x-primary-button>
            </a>
        </div>
    </x-slot>

    <x-ui.page>
        <x-ui.card>
            <form method="GET" action="{{ route('admin.reports.index') }}" class="flex flex-col flex-wrap items-end gap-4 sm:flex-row">
                <div>
                    <x-input-label for="from" :value="__('From date')" />
                    <x-text-input id="from" name="from" type="date" class="mt-1 block w-full" :value="$from" />
                </div>
                <div>
                    <x-input-label for="to" :value="__('To date')" />
                    <x-text-input id="to" name="to" type="date" class="mt-1 block w-full" :value="$to" />
                </div>
                <div class="flex gap-2">
                    <x-primary-button>{{ __('Filter') }}</x-primary-button>
                    <a href="{{ route('admin.reports.index') }}"><x-secondary-button type="button">{{ __('Reset') }}</x-secondary-button></a>
                </div>
            </form>
        </x-ui.card>

        <x-ui.card>
            <h3 class="ui-card-title">{{ __('HR attendance summary') }}</h3>
            @if (! empty($attendanceSummary))
                <dl class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($attendanceSummary as $key => $item)
                        <div class="ui-stat">
                            <dt class="ui-stat-label">{{ is_array($item) ? ($item['label'] ?? $key) : $key }}</dt>
                            <dd class="ui-stat-value">{{ is_array($item) ? ($item['value'] ?? '') : $item }}</dd>
                        </div>
                    @endforeach
                </dl>
            @else
                <p class="mt-4 text-sm text-gray-500">{{ __('No attendance summary data available.') }}</p>
            @endif
        </x-ui.card>

        <x-ui.card>
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <h3 class="ui-card-title">{{ __('Employee attendance audit') }}</h3>
                <a href="{{ route('admin.reports.audit', ['from' => $from, 'to' => $to]) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                    {{ __('Open full audit log') }}
                </a>
            </div>
            @if (! empty($auditSummary))
                <dl class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($auditSummary as $key => $item)
                        <div class="ui-stat">
                            <dt class="ui-stat-label">{{ $key }}</dt>
                            <dd class="ui-stat-value">{{ $item }}</dd>
                        </div>
                    @endforeach
                </dl>
            @endif
            <p class="mt-4 text-sm text-gray-500">
                {{ __('Includes successful and failed clock/QR attempts, plus blocked off-network requests.') }}
            </p>
        </x-ui.card>

        <x-ui.card>
            <h3 class="ui-card-title">{{ __('Finance payroll summary') }}</h3>
            @if (! empty($payrollSummary))
                <dl class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($payrollSummary as $key => $item)
                        <div class="ui-stat">
                            <dt class="ui-stat-label">{{ is_array($item) ? ($item['label'] ?? $key) : $key }}</dt>
                            <dd class="ui-stat-value">{{ is_array($item) ? ($item['value'] ?? '') : $item }}</dd>
                        </div>
                    @endforeach
                </dl>
            @else
                <p class="mt-4 text-sm text-gray-500">{{ __('No payroll summary data available.') }}</p>
            @endif
        </x-ui.card>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <x-ui.card>
                <h3 class="ui-card-title">{{ __('Payroll by department') }}</h3>
                @if (($payrollByDepartment ?? collect())->isNotEmpty())
                    <div class="mt-4 overflow-x-auto">
                        <table class="ui-table">
                            <thead class="ui-thead">
                                <tr>
                                    <th class="ui-th">{{ __('Department') }}</th>
                                    <th class="ui-th">{{ __('Payslips') }}</th>
                                    <th class="ui-th">{{ __('Overtime') }}</th>
                                    <th class="ui-th">{{ __('Net pay') }}</th>
                                </tr>
                            </thead>
                            <tbody class="ui-tbody">
                                @foreach ($payrollByDepartment as $row)
                                    <tr class="ui-tr">
                                        <td class="ui-td-strong">{{ $row->department_name }}</td>
                                        <td class="ui-td">{{ $row->payslip_count }}</td>
                                        <td class="ui-td">{{ number_format((float) $row->overtime_total, 2) }}</td>
                                        <td class="ui-td">{{ number_format((float) $row->net_total, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="mt-4 text-sm text-gray-500">{{ __('No department payroll data in this range.') }}</p>
                @endif
            </x-ui.card>

            <x-ui.card>
                <h3 class="ui-card-title">{{ __('Deduction breakdown') }}</h3>
                @if (($deductionBreakdown ?? collect())->isNotEmpty())
                    <dl class="mt-4 space-y-3">
                        @foreach ($deductionBreakdown as $row)
                            <div class="flex items-center justify-between gap-3 border-b border-gray-100 pb-2 last:border-0">
                                <dt class="text-sm text-gray-600">{{ $row->label ?: $row->code }}</dt>
                                <dd class="text-sm font-semibold tabular-nums text-gray-900">{{ number_format((float) $row->total, 2) }}</dd>
                            </div>
                        @endforeach
                    </dl>
                @else
                    <p class="mt-4 text-sm text-gray-500">{{ __('No deductions in this range.') }}</p>
                @endif
            </x-ui.card>
        </div>

        <x-ui.card>
            <h3 class="ui-card-title">{{ __('Monthly payroll trend') }}</h3>
            @if (($monthlyTrend ?? collect())->isNotEmpty())
                <div class="mt-4 overflow-x-auto">
                    <table class="ui-table">
                        <thead class="ui-thead">
                            <tr>
                                <th class="ui-th">{{ __('Month') }}</th>
                                <th class="ui-th">{{ __('Net pay') }}</th>
                                <th class="ui-th">{{ __('Overtime') }}</th>
                                <th class="ui-th">{{ __('Deductions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="ui-tbody">
                            @foreach ($monthlyTrend as $row)
                                <tr class="ui-tr">
                                    <td class="ui-td-strong">{{ $row->month_key }}</td>
                                    <td class="ui-td">{{ number_format((float) $row->net_total, 2) }}</td>
                                    <td class="ui-td">{{ number_format((float) $row->overtime_total, 2) }}</td>
                                    <td class="ui-td">{{ number_format((float) $row->deductions_total, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="mt-4 text-sm text-gray-500">{{ __('No monthly trend data in this range.') }}</p>
            @endif
        </x-ui.card>
    </x-ui.page>
</x-app-layout>
