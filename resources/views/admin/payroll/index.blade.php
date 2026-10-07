@php
    $openModal = old('form_modal', request('modal') === 'create' ? 'create-payroll' : null);
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-900">{{ __('Payroll') }}</h2>
            <x-primary-button type="button" x-data x-on:click="$dispatch('open-modal', 'create-payroll')">
                {{ __('New payroll run') }}
            </x-primary-button>
        </div>
    </x-slot>

    <x-ui.page>
        <x-ui.flash />

        <x-ui.datatable :paginator="$payrollPeriods" :search-placeholder="__('Search payroll periods…')">
            <table class="ui-table">
                <thead class="ui-thead">
                    <tr>
                        <th class="ui-th">{{ __('Period') }}</th>
                        <th class="ui-th">{{ __('Start') }}</th>
                        <th class="ui-th">{{ __('End') }}</th>
                        <th class="ui-th">{{ __('Status') }}</th>
                        <th class="ui-th-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="ui-tbody">
                    @forelse ($payrollPeriods as $period)
                        @php
                            $periodStatus = strtolower((string) ($period->status?->value ?? $period->status ?? ''));
                            $periodTone = match ($periodStatus) {
                                'finalized', 'paid', 'completed' => 'green',
                                'pending_approval' => 'indigo',
                                'draft' => 'gray',
                                default => 'indigo',
                            };
                            $periodLabel = $period->status?->label() ?? ($period->status ?? '—');
                        @endphp
                        <tr class="ui-tr">
                            <td class="ui-td-strong">{{ $period->name }}</td>
                            <td class="ui-td">{{ $period->start_date?->format('M j, Y') }}</td>
                            <td class="ui-td">{{ $period->end_date?->format('M j, Y') }}</td>
                            <td class="ui-td">
                                <x-ui.badge :tone="$periodTone">{{ $periodLabel }}</x-ui.badge>
                            </td>
                            <td class="ui-td-right">
                                <div class="ui-actions">
                                    <x-ui.table-action :href="route('admin.payroll.show', $period)" icon="document">
                                        {{ __('View payslips') }}
                                    </x-ui.table-action>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr class="ui-tr">
                            <td colspan="5" class="ui-td-empty">{{ __('No payroll periods yet.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </x-ui.datatable>

        <x-ui.form-modal
            name="create-payroll"
            :title="__('Create payroll run')"
            :show="$openModal === 'create-payroll'"
            max-width="lg"
        >
            @include('admin.payroll.partials.form')
        </x-ui.form-modal>
    </x-ui.page>
</x-app-layout>
