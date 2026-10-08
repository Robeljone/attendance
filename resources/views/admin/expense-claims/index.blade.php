<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-900">{{ __('Expense claims') }}</h2>
    </x-slot>

    <x-ui.page>
        <x-ui.flash />

        <x-ui.datatable :paginator="$claims" :search-placeholder="__('Search by employee, title, or category…')">
            <table class="ui-table">
                <thead class="ui-thead">
                    <tr>
                        <th class="ui-th">{{ __('Employee') }}</th>
                        <th class="ui-th">{{ __('Title') }}</th>
                        <th class="ui-th">{{ __('Category') }}</th>
                        <th class="ui-th">{{ __('Amount') }}</th>
                        <th class="ui-th">{{ __('Date') }}</th>
                        <th class="ui-th">{{ __('Status') }}</th>
                        <th class="ui-th">{{ __('Receipt') }}</th>
                        <th class="ui-th-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="ui-tbody">
                    @forelse ($claims as $claim)
                        @php
                            $statusTone = match ($claim->status->value) {
                                'pending' => 'amber',
                                'approved' => 'green',
                                'paid' => 'green',
                                'rejected' => 'red',
                                default => 'gray',
                            };
                        @endphp
                        <tr class="ui-tr">
                            <td class="ui-td-strong">{{ $claim->employee?->user?->name ?? '—' }}</td>
                            <td class="ui-td">{{ $claim->title }}</td>
                            <td class="ui-td">{{ ucfirst($claim->category) }}</td>
                            <td class="ui-td">{{ number_format((float) $claim->amount, 2) }}</td>
                            <td class="ui-td">{{ $claim->expense_date?->format('M j, Y') }}</td>
                            <td class="ui-td">
                                <x-ui.badge :tone="$statusTone">{{ $claim->status->label() }}</x-ui.badge>
                            </td>
                            <td class="ui-td">
                                @if ($claim->hasReceipt())
                                    <a href="{{ route('admin.expense-claims.receipt', $claim) }}" class="ui-link">{{ __('Download') }}</a>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="ui-td-right">
                                @if ($claim->status === \App\Enums\ExpenseClaimStatus::Pending)
                                    <div class="ui-actions">
                                        <form method="POST" action="{{ route('admin.expense-claims.approve', $claim) }}">
                                            @csrf
                                            <x-ui.table-action icon="check" tone="success">{{ __('Approve') }}</x-ui.table-action>
                                        </form>
                                        <form method="POST" action="{{ route('admin.expense-claims.reject', $claim) }}">
                                            @csrf
                                            <x-ui.table-action icon="x-mark" tone="danger">{{ __('Reject') }}</x-ui.table-action>
                                        </form>
                                    </div>
                                @else
                                    <span class="text-gray-400">{{ __('—') }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr class="ui-tr">
                            <td colspan="8" class="ui-td-empty">{{ __('No expense claims.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </x-ui.datatable>
    </x-ui.page>
</x-app-layout>
