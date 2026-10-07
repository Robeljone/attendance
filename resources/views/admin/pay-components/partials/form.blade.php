@php
    /** @var \App\Models\PayComponent|null $payComponent */
    $prefix = $prefix ?? ($payComponent ? 'edit-'.$payComponent->id : 'create');
    $modalName = $payComponent ? 'edit-pay-component-'.$payComponent->id : 'create-pay-component';
    $action = $payComponent
        ? route('admin.pay-components.update', $payComponent)
        : route('admin.pay-components.store');
    $activeForm = old('form_modal') === $modalName;
    $value = fn (string $key, mixed $default = null) => $activeForm ? old($key, $default) : $default;
@endphp

<form method="POST" action="{{ $action }}" class="space-y-5">
    @csrf
    @if ($payComponent)
        @method('PUT')
    @endif
    <input type="hidden" name="form_modal" value="{{ $modalName }}">

    <div>
        <x-input-label for="{{ $prefix }}-name" :value="__('Name')" />
        <x-text-input id="{{ $prefix }}-name" name="name" type="text" class="mt-1 block w-full" :value="$value('name', $payComponent?->name)" required />
        @if ($activeForm)
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        @endif
    </div>

    <div>
        <x-input-label for="{{ $prefix }}-code" :value="__('Code')" />
        <x-text-input id="{{ $prefix }}-code" name="code" type="text" class="mt-1 block w-full" :value="$value('code', $payComponent?->code)" required />
        @if ($activeForm)
            <x-input-error class="mt-2" :messages="$errors->get('code')" />
        @endif
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div>
            <x-input-label for="{{ $prefix }}-type" :value="__('Type')" />
            <x-ui.select id="{{ $prefix }}-type" name="type" required>
                @foreach ($types ?? [] as $type)
                    <option value="{{ $type->value }}" @selected($value('type', $payComponent?->type?->value) === $type->value)>{{ $type->label() }}</option>
                @endforeach
            </x-ui.select>
            @if ($activeForm)
                <x-input-error class="mt-2" :messages="$errors->get('type')" />
            @endif
        </div>
        <div>
            <x-input-label for="{{ $prefix }}-calculation" :value="__('Calculation')" />
            <x-ui.select id="{{ $prefix }}-calculation" name="calculation" required>
                @foreach ($calculations ?? [] as $calculation)
                    <option value="{{ $calculation->value }}" @selected($value('calculation', $payComponent?->calculation?->value ?? 'fixed') === $calculation->value)>{{ $calculation->label() }}</option>
                @endforeach
            </x-ui.select>
            @if ($activeForm)
                <x-input-error class="mt-2" :messages="$errors->get('calculation')" />
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div>
            <x-input-label for="{{ $prefix }}-default_amount" :value="__('Default amount / percent')" />
            <x-text-input id="{{ $prefix }}-default_amount" name="default_amount" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="$value('default_amount', $payComponent?->default_amount ?? 0)" required />
            @if ($activeForm)
                <x-input-error class="mt-2" :messages="$errors->get('default_amount')" />
            @endif
        </div>
        <div>
            <x-input-label for="{{ $prefix }}-sort_order" :value="__('Sort order')" />
            <x-text-input id="{{ $prefix }}-sort_order" name="sort_order" type="number" min="0" class="mt-1 block w-full" :value="$value('sort_order', $payComponent?->sort_order ?? 0)" />
            @if ($activeForm)
                <x-input-error class="mt-2" :messages="$errors->get('sort_order')" />
            @endif
        </div>
    </div>

    <div class="flex flex-wrap items-center gap-6">
        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
            <input type="checkbox" name="is_taxable" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" @checked($value('is_taxable', $payComponent?->is_taxable ?? true)) />
            {{ __('Taxable (earnings)') }}
        </label>
        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
            <input type="checkbox" name="is_active" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" @checked($value('is_active', $payComponent?->is_active ?? true)) />
            {{ __('Active') }}
        </label>
    </div>

    <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-4">
        <x-secondary-button type="button" x-on:click="$dispatch('close')">{{ __('Cancel') }}</x-secondary-button>
        <x-primary-button>{{ $payComponent ? __('Save') : __('Create') }}</x-primary-button>
    </div>
</form>
