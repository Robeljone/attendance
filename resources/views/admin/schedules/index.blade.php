@php
    $openModal = old('form_modal', request('modal') === 'create' ? 'create-schedule' : (
        request('modal') === 'edit' && request()->filled('id')
            ? 'edit-schedule-'.request('id')
            : null
    ));
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-900">{{ __('Work schedules') }}</h2>
            <x-primary-button type="button" x-data x-on:click="$dispatch('open-modal', 'create-schedule')">
                {{ __('Add schedule') }}
            </x-primary-button>
        </div>
    </x-slot>

    <x-ui.page>
        <x-ui.flash />

        <x-ui.datatable :paginator="$schedules" :search-placeholder="__('Search schedules…')">
            <table class="ui-table">
                <thead class="ui-thead">
                    <tr>
                        <th class="ui-th">{{ __('Name') }}</th>
                        <th class="ui-th">{{ __('Hours') }}</th>
                        <th class="ui-th">{{ __('Work days') }}</th>
                        <th class="ui-th">{{ __('Active') }}</th>
                        <th class="ui-th-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="ui-tbody">
                    @php
                        $dayLabels = [1 => 'Mon', 2 => 'Tue', 3 => 'Wed', 4 => 'Thu', 5 => 'Fri', 6 => 'Sat', 7 => 'Sun'];
                    @endphp
                    @forelse ($schedules as $schedule)
                        <tr class="ui-tr">
                            <td class="ui-td-strong">{{ $schedule->name }}</td>
                            <td class="ui-td">{{ $schedule->start_time }} – {{ $schedule->end_time }}</td>
                            <td class="ui-td">
                                <span class="inline-flex flex-wrap gap-1">
                                    @foreach ((array) ($schedule->work_days ?? []) as $day)
                                        <x-ui.badge tone="gray">{{ $dayLabels[(int) $day] ?? $day }}</x-ui.badge>
                                    @endforeach
                                </span>
                            </td>
                            <td class="ui-td">
                                <x-ui.badge :tone="$schedule->is_active ? 'green' : 'gray'">
                                    {{ $schedule->is_active ? __('Yes') : __('No') }}
                                </x-ui.badge>
                            </td>
                            <td class="ui-td-right">
                                <div class="ui-actions">
                                    <button type="button" class="ui-action" x-data x-on:click="$dispatch('open-modal', 'edit-schedule-{{ $schedule->id }}')">
                                        <svg class="ui-action-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                        </svg>
                                        <span>{{ __('Edit') }}</span>
                                    </button>
                                    <form method="POST" action="{{ route('admin.schedules.destroy', $schedule) }}" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <x-ui.table-action icon="trash" tone="danger" :confirm="__('Delete this schedule?')">
                                            {{ __('Delete') }}
                                        </x-ui.table-action>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr class="ui-tr">
                            <td colspan="5" class="ui-td-empty">{{ __('No schedules found.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </x-ui.datatable>

        <x-ui.form-modal
            name="create-schedule"
            :title="__('Add work schedule')"
            :show="$openModal === 'create-schedule'"
            max-width="lg"
        >
            @include('admin.schedules.partials.form', ['schedule' => null])
        </x-ui.form-modal>

        @foreach ($schedules as $schedule)
            <x-ui.form-modal
                name="edit-schedule-{{ $schedule->id }}"
                :title="__('Edit work schedule')"
                :show="$openModal === 'edit-schedule-'.$schedule->id"
                max-width="lg"
            >
                @include('admin.schedules.partials.form', ['schedule' => $schedule])
            </x-ui.form-modal>
        @endforeach

        @if ($editingSchedule ?? null)
            @unless ($schedules->contains('id', $editingSchedule->id))
                <x-ui.form-modal
                    name="edit-schedule-{{ $editingSchedule->id }}"
                    :title="__('Edit work schedule')"
                    :show="true"
                    max-width="lg"
                >
                    @include('admin.schedules.partials.form', ['schedule' => $editingSchedule])
                </x-ui.form-modal>
            @endunless
        @endif
    </x-ui.page>
</x-app-layout>
