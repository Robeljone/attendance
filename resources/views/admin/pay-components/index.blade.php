@php
    $openModal = old('form_modal', request('modal') === 'create' ? 'create-pay-component' : (
        request('modal') === 'edit' && request()->filled('id')
            ? 'edit-pay-component-'.request('id')
            : null
    ));
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-900">{{ __('Pay components') }}</h2>
            <x-primary-button type="button" x-data x-on:click="$dispatch('open-modal', 'create-pay-component')">
                {{ __('Add component') }}
            </x-primary-button>
        </div>
    </x-slot>

    <x-ui.page>
        <x-ui.flash />

        <x-ui.datatable :paginator="$components" :search-placeholder="__('Search by name or code…')">
            <table class="ui-table">
                <thead class="ui-thead">
                    <tr>
                        <th class="ui-th">{{ __('Name') }}</th>
                        <th class="ui-th">{{ __('Code') }}</th>
                        <th class="ui-th">{{ __('Type') }}</th>
                        <th class="ui-th">{{ __('Calculation') }}</th>
                        <th class="ui-th">{{ __('Default') }}</th>
                        <th class="ui-th">{{ __('Active') }}</th>
                        <th class="ui-th-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="ui-tbody">
                    @forelse ($components as $payComponent)
                        <tr class="ui-tr">
                            <td class="ui-td-strong">{{ $payComponent->name }}</td>
                            <td class="ui-td">{{ $payComponent->code }}</td>
                            <td class="ui-td">{{ $payComponent->type->label() }}</td>
                            <td class="ui-td">{{ $payComponent->calculation->label() }}</td>
                            <td class="ui-td">{{ number_format((float) $payComponent->default_amount, 2) }}</td>
                            <td class="ui-td">
                                <x-ui.badge :tone="$payComponent->is_active ? 'green' : 'gray'">
                                    {{ $payComponent->is_active ? __('Yes') : __('No') }}
                                </x-ui.badge>
                            </td>
                            <td class="ui-td-right">
                                <div class="ui-actions">
                                    <button type="button" class="ui-action" x-data x-on:click="$dispatch('open-modal', 'edit-pay-component-{{ $payComponent->id }}')">
                                        <span>{{ __('Edit') }}</span>
                                    </button>
                                    <form method="POST" action="{{ route('admin.pay-components.destroy', $payComponent) }}" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <x-ui.table-action icon="trash" tone="danger" :confirm="__('Delete this pay component?')">
                                            {{ __('Delete') }}
                                        </x-ui.table-action>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr class="ui-tr">
                            <td colspan="7" class="ui-td-empty">{{ __('No pay components yet.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </x-ui.datatable>

        <x-ui.form-modal
            name="create-pay-component"
            :title="__('Add pay component')"
            :show="$openModal === 'create-pay-component'"
            max-width="lg"
        >
            @include('admin.pay-components.partials.form', ['payComponent' => null])
        </x-ui.form-modal>

        @foreach ($components as $payComponent)
            <x-ui.form-modal
                name="edit-pay-component-{{ $payComponent->id }}"
                :title="__('Edit pay component')"
                :show="$openModal === 'edit-pay-component-'.$payComponent->id"
                max-width="lg"
            >
                @include('admin.pay-components.partials.form', ['payComponent' => $payComponent])
            </x-ui.form-modal>
        @endforeach

        @if ($editingComponent ?? null)
            @unless ($components->contains('id', $editingComponent->id))
                <x-ui.form-modal
                    name="edit-pay-component-{{ $editingComponent->id }}"
                    :title="__('Edit pay component')"
                    :show="true"
                    max-width="lg"
                >
                    @include('admin.pay-components.partials.form', ['payComponent' => $editingComponent])
                </x-ui.form-modal>
            @endunless
        @endif
    </x-ui.page>
</x-app-layout>
