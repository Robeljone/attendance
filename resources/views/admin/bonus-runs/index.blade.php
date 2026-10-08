@php
    $openModal = old('form_modal', request('modal') === 'create' ? 'create-bonus-run' : null);
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-900">{{ __('Bonus runs') }}</h2>
            <x-primary-button type="button" x-data x-on:click="$dispatch('open-modal', 'create-bonus-run')">
                {{ __('New bonus run') }}
            </x-primary-button>
        </div>
    </x-slot>

    <x-ui.page>
        <x-ui.flash />

        <x-ui.datatable :paginator="$bonusRuns" :search-placeholder="__('Search by name…')">
            <table class="ui-table">
                <thead class="ui-thead">
                    <tr>
                        <th class="ui-th">{{ __('Name') }}</th>
                        <th class="ui-th">{{ __('Payroll period') }}</th>
                        <th class="ui-th">{{ __('Employees') }}</th>
                        <th class="ui-th">{{ __('Total') }}</th>
                        <th class="ui-th">{{ __('Status') }}</th>
                        <th class="ui-th-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="ui-tbody">
                    @forelse ($bonusRuns as $bonusRun)
                        <tr class="ui-tr">
                            <td class="ui-td-strong">{{ $bonusRun->name }}</td>
                            <td class="ui-td">{{ $bonusRun->payrollPeriod?->name ?? '—' }}</td>
                            <td class="ui-td">{{ $bonusRun->items_count }}</td>
                            <td class="ui-td">{{ number_format((float) ($bonusRun->items_sum_amount ?? 0), 2) }}</td>
                            <td class="ui-td">
                                <x-ui.badge :tone="$bonusRun->isDraft() ? 'amber' : 'green'">
                                    {{ $bonusRun->status->label() }}
                                </x-ui.badge>
                            </td>
                            <td class="ui-td-right">
                                <div class="ui-actions">
                                    @if ($bonusRun->isDraft())
                                        <form method="POST" action="{{ route('admin.bonus-runs.apply', $bonusRun) }}">
                                            @csrf
                                            <x-ui.table-action icon="check" tone="success" :confirm="__('Apply this bonus run to the draft payroll?')">
                                                {{ __('Apply') }}
                                            </x-ui.table-action>
                                        </form>
                                        <form method="POST" action="{{ route('admin.bonus-runs.destroy', $bonusRun) }}">
                                            @csrf
                                            @method('DELETE')
                                            <x-ui.table-action icon="trash" tone="danger" :confirm="__('Delete this bonus run?')">
                                                {{ __('Delete') }}
                                            </x-ui.table-action>
                                        </form>
                                    @else
                                        <span class="text-gray-400">{{ __('—') }}</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr class="ui-tr">
                            <td colspan="6" class="ui-td-empty">{{ __('No bonus runs yet.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </x-ui.datatable>

        <x-ui.form-modal name="create-bonus-run" :title="__('New bonus run')" :show="$openModal === 'create-bonus-run'" max-width="2xl">
            <form
                method="POST"
                action="{{ route('admin.bonus-runs.store') }}"
                class="space-y-5"
                x-data="{ rows: [{ employee_id: '', amount: '', label: '' }] }"
            >
                @csrf
                <input type="hidden" name="form_modal" value="create-bonus-run">

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <x-input-label for="bonus-name" :value="__('Name')" />
                        <x-text-input id="bonus-name" name="name" type="text" class="mt-1 block w-full" :value="old('name')" required />
                    </div>
                    <div>
                        <x-input-label for="bonus-period" :value="__('Draft payroll period')" />
                        <select id="bonus-period" name="payroll_period_id" class="ui-select mt-1 block w-full" required>
                            <option value="">{{ __('Select period') }}</option>
                            @foreach ($periods as $period)
                                <option value="{{ $period->id }}" @selected(old('payroll_period_id') == $period->id)>{{ $period->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <x-input-label for="bonus-notes" :value="__('Notes')" />
                    <textarea id="bonus-notes" name="notes" rows="2" class="ui-textarea mt-1 block w-full">{{ old('notes') }}</textarea>
                </div>

                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <h4 class="text-sm font-semibold text-gray-900">{{ __('Employees') }}</h4>
                        <button type="button" class="text-sm font-medium text-indigo-600" x-on:click="rows.push({ employee_id: '', amount: '', label: '' })">
                            {{ __('Add row') }}
                        </button>
                    </div>
                    <template x-for="(row, index) in rows" :key="index">
                        <div class="grid grid-cols-1 gap-3 rounded-lg border border-gray-200 p-3 sm:grid-cols-3">
                            <select class="ui-select block w-full" :name="`items[${index}][employee_id]`" x-model="row.employee_id" required>
                                <option value="">{{ __('Employee') }}</option>
                                @foreach ($employees as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->user?->name }}</option>
                                @endforeach
                            </select>
                            <input type="number" step="0.01" min="0.01" class="ui-input block w-full" :name="`items[${index}][amount]`" x-model="row.amount" placeholder="{{ __('Amount') }}" required />
                            <div class="flex gap-2">
                                <input type="text" class="ui-input block w-full" :name="`items[${index}][label]`" x-model="row.label" placeholder="{{ __('Label (optional)') }}" />
                                <button type="button" class="text-sm text-red-600" x-show="rows.length > 1" x-on:click="rows.splice(index, 1)">{{ __('Remove') }}</button>
                            </div>
                        </div>
                    </template>
                </div>

                <x-primary-button>{{ __('Create draft') }}</x-primary-button>
            </form>
        </x-ui.form-modal>
    </x-ui.page>
</x-app-layout>
