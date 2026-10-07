<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-900">{{ __('Attendance') }}</h2>
    </x-slot>

    <x-ui.page>
        <x-ui.flash />

        <x-ui.card>
            <form method="GET" action="{{ route('admin.attendance.index') }}" class="flex flex-col flex-wrap items-end gap-4 sm:flex-row">
                <input type="hidden" name="q" value="{{ request('q') }}">
                <input type="hidden" name="per_page" value="{{ request('per_page', 20) }}">
                <div>
                    <x-input-label for="from" :value="__('From date')" />
                    <x-text-input id="from" name="from" type="date" class="mt-1 block w-full" :value="request('from')" />
                </div>
                <div>
                    <x-input-label for="to" :value="__('To date')" />
                    <x-text-input id="to" name="to" type="date" class="mt-1 block w-full" :value="request('to')" />
                </div>
                <div class="flex gap-2">
                    <x-primary-button>{{ __('Filter') }}</x-primary-button>
                    <a href="{{ route('admin.attendance.index') }}"><x-secondary-button type="button">{{ __('Reset') }}</x-secondary-button></a>
                </div>
            </form>
        </x-ui.card>

        <x-ui.datatable :paginator="$attendanceRecords" :search-placeholder="__('Search by employee name or number…')">
            <table class="ui-table">
                <thead class="ui-thead">
                    <tr>
                        <th class="ui-th">{{ __('Employee') }}</th>
                        <th class="ui-th">{{ __('Date') }}</th>
                        <th class="ui-th">{{ __('Clock in') }}</th>
                        <th class="ui-th">{{ __('Clock out') }}</th>
                        <th class="ui-th">{{ __('Method') }}</th>
                        <th class="ui-th">{{ __('Worked') }}</th>
                    </tr>
                </thead>
                <tbody class="ui-tbody">
                    @forelse ($attendanceRecords as $record)
                        <tr class="ui-tr">
                            <td class="ui-td-strong">{{ $record->employee?->user?->name ?? $record->employee?->employee_number ?? '—' }}</td>
                            <td class="ui-td">{{ $record->work_date?->format('M j, Y') ?? '—' }}</td>
                            <td class="ui-td">{{ $record->clock_in_at?->format('Y-m-d H:i') ?? '—' }}</td>
                            <td class="ui-td">{{ $record->clock_out_at?->format('Y-m-d H:i') ?? '—' }}</td>
                            <td class="ui-td">
                                {{ $record->clock_in_method instanceof \BackedEnum ? $record->clock_in_method->value : ($record->clock_in_method ?? '—') }}
                            </td>
                            <td class="ui-td">
                                @if ($record->worked_minutes)
                                    {{ floor($record->worked_minutes / 60) }}h {{ $record->worked_minutes % 60 }}m
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr class="ui-tr">
                            <td colspan="6" class="ui-td-empty">{{ __('No attendance records for this period.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </x-ui.datatable>
    </x-ui.page>
</x-app-layout>
