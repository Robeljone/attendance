@php
    $openModal = old('form_modal', request('modal') === 'create' ? 'create-salary-increment' : null);
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-900">{{ __('Salary increments') }}</h2>
            <x-primary-button type="button" x-data x-on:click="$dispatch('open-modal', 'create-salary-increment')">
                {{ __('Schedule increment') }}
            </x-primary-button>
        </div>
    </x-slot>

    <x-ui.page>
        <x-ui.flash />

        <x-ui.datatable :paginator="$increments" :search-placeholder="__('Search by employee…')">
            <table class="ui-table">
                <thead class="ui-thead">
                    <tr>
                        <th class="ui-th">{{ __('Employee') }}</th>
                        <th class="ui-th">{{ __('Type') }}</th>
                        <th class="ui-th">{{ __('Amount') }}</th>
                        <th class="ui-th">{{ __('Effective') }}</th>
                        <th class="ui-th">{{ __('Status') }}</th>
                        <th class="ui-th-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="ui-tbody">
                    @forelse ($increments as $increment)
                        <tr class="ui-tr">
                            <td class="ui-td-strong">{{ $increment->employee?->user?->name ?? '—' }}</td>
                            <td class="ui-td">{{ $increment->type->label() }}</td>
                            <td class="ui-td">
                                {{ number_format((float) $increment->amount, 2) }}
                                {{ $increment->type->value === 'percent' ? '%' : '' }}
                            </td>
                            <td class="ui-td">{{ $increment->effective_date?->format('M j, Y') }}</td>
                            <td class="ui-td">
                                <x-ui.badge :tone="match ($increment->status->value) {
                                    'pending' => 'amber',
                                    'applied' => 'green',
                                    default => 'gray',
                                }">
                                    {{ $increment->status->label() }}
                                </x-ui.badge>
                            </td>
                            <td class="ui-td-right">
                                <div class="ui-actions">
                                    @if ($increment->isPending())
                                        <form method="POST" action="{{ route('admin.salary-increments.apply', $increment) }}">
                                            @csrf
                                            <x-ui.table-action icon="check" tone="success" :confirm="__('Apply this increment to base salary?')">
                                                {{ __('Apply') }}
                                            </x-ui.table-action>
                                        </form>
                                        <form method="POST" action="{{ route('admin.salary-increments.destroy', $increment) }}">
                                            @csrf
                                            @method('DELETE')
                                            <x-ui.table-action icon="trash" tone="danger" :confirm="__('Delete this increment?')">
                                                {{ __('Delete') }}
                                            </x-ui.table-action>
                                        </form>
                                    @elseif ($increment->new_base_salary)
                                        <span class="text-sm text-gray-500">
                                            {{ number_format((float) $increment->previous_base_salary, 2) }}
                                            → {{ number_format((float) $increment->new_base_salary, 2) }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">{{ __('—') }}</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr class="ui-tr">
                            <td colspan="6" class="ui-td-empty">{{ __('No salary increments yet.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </x-ui.datatable>

        <x-ui.form-modal name="create-salary-increment" :title="__('Schedule salary increment')" :show="$openModal === 'create-salary-increment'" max-width="lg">
            <form method="POST" action="{{ route('admin.salary-increments.store') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="form_modal" value="create-salary-increment">

                <div>
                    <x-input-label for="increment-employee" :value="__('Employee')" />
                    <select id="increment-employee" name="employee_id" class="ui-select mt-1 block w-full" required>
                        <option value="">{{ __('Select employee') }}</option>
                        @foreach ($employees as $employee)
                            <option value="{{ $employee->id }}" @selected(old('employee_id') == $employee->id)>
                                {{ $employee->user?->name }} — {{ number_format((float) $employee->base_salary, 2) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <x-input-label for="increment-type" :value="__('Type')" />
                        <select id="increment-type" name="type" class="ui-select mt-1 block w-full" required>
                            @foreach ($types as $type)
                                <option value="{{ $type->value }}" @selected(old('type', 'fixed') === $type->value)>{{ $type->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="increment-amount" :value="__('Amount')" />
                        <x-text-input id="increment-amount" name="amount" type="number" step="0.01" min="0.01" class="mt-1 block w-full" :value="old('amount')" required />
                    </div>
                </div>

                <div>
                    <x-input-label for="increment-effective" :value="__('Effective date')" />
                    <x-text-input id="increment-effective" name="effective_date" type="date" class="mt-1 block w-full" :value="old('effective_date', now()->toDateString())" required />
                </div>

                <div>
                    <x-input-label for="increment-notes" :value="__('Notes')" />
                    <textarea id="increment-notes" name="notes" rows="2" class="ui-textarea mt-1 block w-full">{{ old('notes') }}</textarea>
                </div>

                <x-primary-button>{{ __('Schedule') }}</x-primary-button>
            </form>
        </x-ui.form-modal>
    </x-ui.page>
</x-app-layout>
