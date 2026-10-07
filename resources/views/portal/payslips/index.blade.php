<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-900">{{ __('My payslips') }}</h2>
    </x-slot>

    <x-ui.page>
        <x-ui.card flush>
            <div class="ui-table-wrap">
                <table class="ui-table">
                    <thead class="ui-thead">
                        <tr>
                            <th class="ui-th">{{ __('Period') }}</th>
                            <th class="ui-th">{{ __('Net pay') }}</th>
                            <th class="ui-th-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="ui-tbody">
                        @forelse ($payslips ?? [] as $payslip)
                            <tr class="ui-tr">
                                <td class="ui-td-strong">{{ $payslip->payrollPeriod?->name ?? '—' }}</td>
                                <td class="ui-td">{{ number_format((float) ($payslip->net_pay ?? 0), 2) }}</td>
                                <td class="ui-td-right">
                                    <div class="ui-actions">
                                        <x-ui.table-action :href="route('portal.payslips.show', $payslip)" icon="eye">
                                            {{ __('View') }}
                                        </x-ui.table-action>
                                        <x-ui.table-action :href="route('portal.payslips.print', $payslip)" icon="document">
                                            {{ __('Print') }}
                                        </x-ui.table-action>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr class="ui-tr">
                                <td colspan="3" class="ui-td-empty">{{ __('No payslips available.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.card>
    </x-ui.page>
</x-app-layout>
