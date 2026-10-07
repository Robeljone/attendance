@php
    $openModal = old('form_modal', request('modal') === 'create' ? 'create-department' : (
        request('modal') === 'edit' && request()->filled('id')
            ? 'edit-department-'.request('id')
            : null
    ));
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-900">{{ __('Departments') }}</h2>
            <x-primary-button type="button" x-data x-on:click="$dispatch('open-modal', 'create-department')">
                {{ __('Add department') }}
            </x-primary-button>
        </div>
    </x-slot>

    <x-ui.page>
        <x-ui.flash />

        <x-ui.datatable :paginator="$departments" :search-placeholder="__('Search by name or code…')">
            <table class="ui-table">
                <thead class="ui-thead">
                    <tr>
                        <th class="ui-th">{{ __('Name') }}</th>
                        <th class="ui-th">{{ __('Code') }}</th>
                        <th class="ui-th">{{ __('Employees') }}</th>
                        <th class="ui-th">{{ __('Active') }}</th>
                        <th class="ui-th-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="ui-tbody">
                    @forelse ($departments as $department)
                        <tr class="ui-tr">
                            <td class="ui-td-strong">{{ $department->name }}</td>
                            <td class="ui-td">{{ $department->code ?? '—' }}</td>
                            <td class="ui-td">{{ $department->employees_count ?? $department->employees?->count() ?? 0 }}</td>
                            <td class="ui-td">
                                <x-ui.badge :tone="$department->is_active ? 'green' : 'gray'">
                                    {{ $department->is_active ? __('Yes') : __('No') }}
                                </x-ui.badge>
                            </td>
                            <td class="ui-td-right">
                                <div class="ui-actions">
                                    <button type="button" class="ui-action" x-data x-on:click="$dispatch('open-modal', 'edit-department-{{ $department->id }}')">
                                        <svg class="ui-action-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                        </svg>
                                        <span>{{ __('Edit') }}</span>
                                    </button>
                                    <form method="POST" action="{{ route('admin.departments.destroy', $department) }}" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <x-ui.table-action icon="trash" tone="danger" :confirm="__('Delete this department?')">
                                            {{ __('Delete') }}
                                        </x-ui.table-action>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr class="ui-tr">
                            <td colspan="5" class="ui-td-empty">{{ __('No departments found.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </x-ui.datatable>

        <x-ui.form-modal
            name="create-department"
            :title="__('Add department')"
            :show="$openModal === 'create-department'"
            max-width="lg"
        >
            @include('admin.departments.partials.form', ['department' => null])
        </x-ui.form-modal>

        @foreach ($departments as $department)
            <x-ui.form-modal
                name="edit-department-{{ $department->id }}"
                :title="__('Edit department')"
                :show="$openModal === 'edit-department-'.$department->id"
                max-width="lg"
            >
                @include('admin.departments.partials.form', ['department' => $department])
            </x-ui.form-modal>
        @endforeach

        @if ($editingDepartment ?? null)
            @unless ($departments->contains('id', $editingDepartment->id))
                <x-ui.form-modal
                    name="edit-department-{{ $editingDepartment->id }}"
                    :title="__('Edit department')"
                    :show="true"
                    max-width="lg"
                >
                    @include('admin.departments.partials.form', ['department' => $editingDepartment])
                </x-ui.form-modal>
            @endunless
        @endif
    </x-ui.page>
</x-app-layout>
