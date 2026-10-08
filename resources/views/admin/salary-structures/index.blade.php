@php
    $openModal = old('form_modal', request('modal') === 'create' ? 'create-salary-structure' : (
        request('modal') === 'edit' && request()->filled('id')
            ? 'edit-salary-structure-'.request('id')
            : null
    ));
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-900">{{ __('Salary structures') }}</h2>
            <x-primary-button type="button" x-data x-on:click="$dispatch('open-modal', 'create-salary-structure')">
                {{ __('Add structure') }}
            </x-primary-button>
        </div>
    </x-slot>

    <x-ui.page>
        <x-ui.flash />

        <x-ui.datatable :paginator="$structures" :search-placeholder="__('Search by name or code…')">
            <table class="ui-table">
                <thead class="ui-thead">
                    <tr>
                        <th class="ui-th">{{ __('Name') }}</th>
                        <th class="ui-th">{{ __('Code') }}</th>
                        <th class="ui-th">{{ __('Components') }}</th>
                        <th class="ui-th">{{ __('Employees') }}</th>
                        <th class="ui-th">{{ __('Active') }}</th>
                        <th class="ui-th-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="ui-tbody">
                    @forelse ($structures as $structure)
                        <tr class="ui-tr">
                            <td class="ui-td-strong">{{ $structure->name }}</td>
                            <td class="ui-td">{{ $structure->code }}</td>
                            <td class="ui-td">{{ $structure->payComponents->count() }}</td>
                            <td class="ui-td">{{ $structure->employees_count }}</td>
                            <td class="ui-td">
                                <x-ui.badge :tone="$structure->is_active ? 'green' : 'gray'">
                                    {{ $structure->is_active ? __('Yes') : __('No') }}
                                </x-ui.badge>
                            </td>
                            <td class="ui-td-right">
                                <div class="ui-actions">
                                    <button type="button" class="ui-action" x-data x-on:click="$dispatch('open-modal', 'apply-salary-structure-{{ $structure->id }}')">
                                        <span>{{ __('Apply') }}</span>
                                    </button>
                                    <button type="button" class="ui-action" x-data x-on:click="$dispatch('open-modal', 'edit-salary-structure-{{ $structure->id }}')">
                                        <span>{{ __('Edit') }}</span>
                                    </button>
                                    <form method="POST" action="{{ route('admin.salary-structures.destroy', $structure) }}" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <x-ui.table-action icon="trash" tone="danger" :confirm="__('Delete this salary structure?')">
                                            {{ __('Delete') }}
                                        </x-ui.table-action>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr class="ui-tr">
                            <td colspan="6" class="ui-td-empty">{{ __('No salary structures yet.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </x-ui.datatable>

        <x-ui.form-modal
            name="create-salary-structure"
            :title="__('Add salary structure')"
            :show="$openModal === 'create-salary-structure'"
            max-width="2xl"
        >
            @include('admin.salary-structures.partials.form', ['structure' => null])
        </x-ui.form-modal>

        @foreach ($structures as $structure)
            <x-ui.form-modal
                name="edit-salary-structure-{{ $structure->id }}"
                :title="__('Edit salary structure')"
                :show="$openModal === 'edit-salary-structure-'.$structure->id || ($editingStructure?->id === $structure->id && str_starts_with((string) old('form_modal'), 'edit-salary-structure-'))"
                max-width="2xl"
            >
                @include('admin.salary-structures.partials.form', ['structure' => $structure])
            </x-ui.form-modal>

            <x-ui.form-modal
                name="apply-salary-structure-{{ $structure->id }}"
                :title="__('Apply :name', ['name' => $structure->name])"
                max-width="md"
            >
                <form method="POST" action="{{ route('admin.salary-structures.apply', $structure) }}" class="space-y-4">
                    @csrf
                    <div>
                        <x-input-label for="employee_id_{{ $structure->id }}" :value="__('Employee')" />
                        <select id="employee_id_{{ $structure->id }}" name="employee_id" class="ui-select mt-1 block w-full" required>
                            <option value="">{{ __('Select employee') }}</option>
                            @foreach ($employees as $employee)
                                <option value="{{ $employee->id }}">{{ $employee->user?->name }} ({{ $employee->employee_number }})</option>
                            @endforeach
                        </select>
                    </div>
                    <p class="text-sm text-gray-500">{{ __('This replaces the employee’s current pay component assignments with the structure package.') }}</p>
                    <x-primary-button>{{ __('Apply structure') }}</x-primary-button>
                </form>
            </x-ui.form-modal>
        @endforeach
    </x-ui.page>
</x-app-layout>
