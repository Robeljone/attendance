<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-900">{{ __('Employees') }}</h2>
            <a href="{{ route('admin.employees.create') }}">
                <x-primary-button type="button">{{ __('Add employee') }}</x-primary-button>
            </a>
        </div>
    </x-slot>

    <x-ui.page>
        <x-ui.flash />

        <x-ui.datatable :paginator="$employees" :search-placeholder="__('Search by name, number, position…')">
            <table class="ui-table">
                <thead class="ui-thead">
                    <tr>
                        <th class="ui-th">{{ __('Name') }}</th>
                        <th class="ui-th">{{ __('Number') }}</th>
                        <th class="ui-th">{{ __('Department') }}</th>
                        <th class="ui-th">{{ __('Position') }}</th>
                        <th class="ui-th">{{ __('Status') }}</th>
                        <th class="ui-th-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="ui-tbody">
                    @forelse ($employees as $employee)
                        @php
                            $empStatus = $employee->status;
                            $empStatusLabel = $empStatus instanceof \App\Enums\EmploymentStatus
                                ? $empStatus->label()
                                : ($empStatus instanceof \BackedEnum ? $empStatus->value : ($empStatus ?? '—'));
                            $empStatusTone = match ($empStatus instanceof \BackedEnum ? $empStatus->value : (string) $empStatus) {
                                'active' => 'green',
                                'on_leave' => 'amber',
                                'terminated' => 'red',
                                default => 'gray',
                            };
                        @endphp
                        <tr class="ui-tr">
                            <td class="ui-td-strong">
                                <div class="flex items-center gap-3">
                                    @if ($employee->photoUrl())
                                        <img src="{{ $employee->photoUrl() }}" alt="" class="h-8 w-8 rounded-full object-cover ring-1 ring-gray-200">
                                    @else
                                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-gray-100 text-xs font-semibold text-gray-500 ring-1 ring-gray-200">
                                            {{ strtoupper(substr($employee->user?->name ?? 'E', 0, 1)) }}
                                        </div>
                                    @endif
                                    <span>{{ $employee->user?->name ?? '—' }}</span>
                                </div>
                            </td>
                            <td class="ui-td">{{ $employee->employee_number }}</td>
                            <td class="ui-td">{{ $employee->department?->name ?? '—' }}</td>
                            <td class="ui-td">{{ $employee->position ?? '—' }}</td>
                            <td class="ui-td">
                                <x-ui.badge :tone="$empStatusTone">{{ $empStatusLabel }}</x-ui.badge>
                            </td>
                            <td class="ui-td-right">
                                <div class="ui-actions">
                                    <x-ui.table-action :href="route('admin.employees.show', $employee)" icon="eye">
                                        {{ __('View') }}
                                    </x-ui.table-action>
                                    <x-ui.table-action :href="route('admin.employees.edit', $employee)" icon="pencil">
                                        {{ __('Edit') }}
                                    </x-ui.table-action>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr class="ui-tr">
                            <td colspan="6" class="ui-td-empty">{{ __('No employees found.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </x-ui.datatable>
    </x-ui.page>
</x-app-layout>
