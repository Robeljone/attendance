@php
    $openModal = old('form_modal', request('modal') === 'create' ? 'create-expense-claim' : null);
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-900">{{ __('My expenses') }}</h2>
            <x-primary-button type="button" x-data x-on:click="$dispatch('open-modal', 'create-expense-claim')">
                {{ __('Submit claim') }}
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
                            <th class="ui-th">{{ __('Title') }}</th>
                            <th class="ui-th">{{ __('Category') }}</th>
                            <th class="ui-th">{{ __('Amount') }}</th>
                            <th class="ui-th">{{ __('Date') }}</th>
                            <th class="ui-th">{{ __('Status') }}</th>
                            <th class="ui-th">{{ __('Receipt') }}</th>
                        </tr>
                    </thead>
                    <tbody class="ui-tbody">
                        @forelse ($claims as $claim)
                            @php
                                $statusTone = match ($claim->status->value) {
                                    'pending' => 'amber',
                                    'approved', 'paid' => 'green',
                                    'rejected' => 'red',
                                    default => 'gray',
                                };
                            @endphp
                            <tr class="ui-tr">
                                <td class="ui-td-strong">{{ $claim->title }}</td>
                                <td class="ui-td">{{ ucfirst($claim->category) }}</td>
                                <td class="ui-td">{{ number_format((float) $claim->amount, 2) }}</td>
                                <td class="ui-td">{{ $claim->expense_date?->format('M j, Y') }}</td>
                                <td class="ui-td">
                                    <x-ui.badge :tone="$statusTone">{{ $claim->status->label() }}</x-ui.badge>
                                </td>
                                <td class="ui-td">
                                    @if ($claim->hasReceipt())
                                        <a href="{{ route('portal.expense-claims.receipt', $claim) }}" class="ui-link">{{ __('Download') }}</a>
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr class="ui-tr">
                                <td colspan="6" class="ui-td-empty">{{ __('No expense claims yet.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if (method_exists($claims, 'links'))
                <div class="ui-pagination">{{ $claims->links() }}</div>
            @endif
        </x-ui.card>

        <x-ui.form-modal name="create-expense-claim" :title="__('Submit expense claim')" :show="$openModal === 'create-expense-claim'" max-width="lg">
            <form method="POST" action="{{ route('portal.expense-claims.store') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <input type="hidden" name="form_modal" value="create-expense-claim">

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <x-input-label for="expense-category" :value="__('Category')" />
                        <select id="expense-category" name="category" class="ui-select mt-1 block w-full" required>
                            @foreach ($categories as $category)
                                <option value="{{ $category }}" @selected(old('category') === $category)>{{ __(ucfirst($category)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="expense-date" :value="__('Expense date')" />
                        <x-text-input id="expense-date" name="expense_date" type="date" class="mt-1 block w-full" :value="old('expense_date', now()->toDateString())" required />
                    </div>
                </div>

                <div>
                    <x-input-label for="expense-title" :value="__('Title')" />
                    <x-text-input id="expense-title" name="title" type="text" class="mt-1 block w-full" :value="old('title')" required />
                </div>

                <div>
                    <x-input-label for="expense-amount" :value="__('Amount')" />
                    <x-text-input id="expense-amount" name="amount" type="number" step="0.01" min="0.01" class="mt-1 block w-full" :value="old('amount')" required />
                </div>

                <div>
                    <x-input-label for="expense-description" :value="__('Description')" />
                    <textarea id="expense-description" name="description" rows="3" class="ui-textarea mt-1 block w-full">{{ old('description') }}</textarea>
                </div>

                <div>
                    <x-input-label for="expense-receipt" :value="__('Receipt (optional)')" />
                    <input id="expense-receipt" name="receipt" type="file" class="mt-1 block w-full text-sm" accept=".jpg,.jpeg,.png,.webp,.pdf" />
                </div>

                <x-primary-button>{{ __('Submit') }}</x-primary-button>
            </form>
        </x-ui.form-modal>
    </x-ui.page>
</x-app-layout>
