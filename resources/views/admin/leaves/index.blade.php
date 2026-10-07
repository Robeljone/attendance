<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-900">{{ __('Leave requests') }}</h2>
    </x-slot>

    <x-ui.page>
        <x-ui.flash />

        <x-ui.datatable :paginator="$leaveRequests" :search-placeholder="__('Search by employee or leave type…')">
            <table class="ui-table">
                <thead class="ui-thead">
                    <tr>
                        <th class="ui-th">{{ __('Employee') }}</th>
                        <th class="ui-th">{{ __('Type') }}</th>
                        <th class="ui-th">{{ __('Dates') }}</th>
                        <th class="ui-th">{{ __('Days') }}</th>
                        <th class="ui-th">{{ __('Status') }}</th>
                        <th class="ui-th">{{ __('Attachment') }}</th>
                        <th class="ui-th-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="ui-tbody">
                    @forelse ($leaveRequests as $leave)
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
                            $isPending = in_array(strtolower($statusValue), ['pending', 'submitted'], true);
                        @endphp
                        <tr class="ui-tr">
                            <td class="ui-td-strong">{{ $leave->employee?->user?->name ?? '—' }}</td>
                            <td class="ui-td">{{ $leave->leaveType?->name ?? '—' }}</td>
                            <td class="ui-td">
                                {{ $leave->start_date?->format('M j, Y') }} – {{ $leave->end_date?->format('M j, Y') }}
                            </td>
                            <td class="ui-td">{{ $leave->days ?? '—' }}</td>
                            <td class="ui-td">
                                <x-ui.badge :tone="$statusTone">{{ $statusLabel }}</x-ui.badge>
                            </td>
                            <td class="ui-td">
                                @if ($leave->hasAttachment())
                                    <a href="{{ route('admin.leaves.attachment', $leave) }}" class="ui-link">{{ __('Download') }}</a>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="ui-td-right">
                                @if ($isPending)
                                    <div class="ui-actions">
                                        <form method="POST" action="{{ route('admin.leaves.approve', $leave) }}">
                                            @csrf
                                            <x-ui.table-action icon="check" tone="success">
                                                {{ __('Approve') }}
                                            </x-ui.table-action>
                                        </form>
                                        <form method="POST" action="{{ route('admin.leaves.reject', $leave) }}">
                                            @csrf
                                            <x-ui.table-action icon="x-mark" tone="danger">
                                                {{ __('Reject') }}
                                            </x-ui.table-action>
                                        </form>
                                    </div>
                                @else
                                    <span class="text-gray-400">{{ __('—') }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr class="ui-tr">
                            <td colspan="7" class="ui-td-empty">{{ __('No leave requests.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </x-ui.datatable>
    </x-ui.page>
</x-app-layout>
