@php
    $openModal = old('form_modal', request('modal') === 'create' ? 'create-leave' : null);
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-900">{{ __('My leaves') }}</h2>
            <x-primary-button type="button" x-data x-on:click="$dispatch('open-modal', 'create-leave')">
                {{ __('Request leave') }}
            </x-primary-button>
        </div>
    </x-slot>

    <x-ui.page>
        <x-ui.flash />

        <x-ui.card flush>
            <div class="ui-table-wrap">
                <table class="ui-table">
                    <thead class="ui-thead">
                        <tr>
                            <th class="ui-th">{{ __('Type') }}</th>
                            <th class="ui-th">{{ __('Dates') }}</th>
                            <th class="ui-th">{{ __('Days') }}</th>
                            <th class="ui-th">{{ __('Status') }}</th>
                            <th class="ui-th">{{ __('Reason') }}</th>
                            <th class="ui-th">{{ __('Attachment') }}</th>
                        </tr>
                    </thead>
                    <tbody class="ui-tbody">
                        @forelse ($leaveRequests ?? [] as $leave)
                            @php
                                $leaveStatus = $leave->status;
                                $statusValue = $leaveStatus instanceof \BackedEnum ? $leaveStatus->value : (string) $leaveStatus;
                                $statusLabel = $leaveStatus instanceof \App\Enums\LeaveStatus
                                    ? $leaveStatus->label()
                                    : ($leaveStatus instanceof \BackedEnum ? $leaveStatus->value : ($leaveStatus ?? '—'));
                                $statusTone = match (strtolower($statusValue)) {
                                    'pending', 'submitted' => 'amber',
                                    'approved' => 'green',
                                    'rejected' => 'red',
                                    'cancelled' => 'gray',
                                    default => 'gray',
                                };
                            @endphp
                            <tr class="ui-tr">
                                <td class="ui-td-strong">{{ $leave->leaveType?->name ?? '—' }}</td>
                                <td class="ui-td">{{ $leave->start_date?->format('M j') }} – {{ $leave->end_date?->format('M j, Y') }}</td>
                                <td class="ui-td">{{ $leave->days ?? '—' }}</td>
                                <td class="ui-td">
                                    <x-ui.badge :tone="$statusTone">{{ $statusLabel }}</x-ui.badge>
                                </td>
                                <td class="ui-td max-w-xs truncate">{{ $leave->reason ?? '—' }}</td>
                                <td class="ui-td">
                                    @if ($leave->hasAttachment())
                                        <a href="{{ route('portal.leaves.attachment', $leave) }}" class="ui-link">{{ __('Download') }}</a>
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr class="ui-tr">
                                <td colspan="6" class="ui-td-empty">{{ __('No leave requests yet.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if (isset($leaveRequests) && method_exists($leaveRequests, 'links'))
                <div class="ui-pagination">{{ $leaveRequests->links() }}</div>
            @endif
        </x-ui.card>

        <x-ui.form-modal
            name="create-leave"
            :title="__('Request leave')"
            :show="$openModal === 'create-leave'"
            max-width="lg"
        >
            @include('portal.leaves.partials.form', ['leaveTypes' => $leaveTypes])
        </x-ui.form-modal>
    </x-ui.page>
</x-app-layout>
