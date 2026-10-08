@php
    $structure = $structure ?? null;
    $modalName = $structure ? 'edit-salary-structure-'.$structure->id : 'create-salary-structure';
    $action = $structure
        ? route('admin.salary-structures.update', $structure)
        : route('admin.salary-structures.store');
    $assigned = $structure?->payComponents?->keyBy('id') ?? collect();
@endphp

<form method="POST" action="{{ $action }}" class="space-y-5">
    @csrf
    @if ($structure)
        @method('PUT')
    @endif
    <input type="hidden" name="form_modal" value="{{ $modalName }}">

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div>
            <x-input-label for="{{ $modalName }}-name" :value="__('Name')" />
            <x-text-input id="{{ $modalName }}-name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $structure?->name)" required />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>
        <div>
            <x-input-label for="{{ $modalName }}-code" :value="__('Code')" />
            <x-text-input id="{{ $modalName }}-code" name="code" type="text" class="mt-1 block w-full" :value="old('code', $structure?->code)" required />
            <x-input-error class="mt-2" :messages="$errors->get('code')" />
        </div>
    </div>

    <div>
        <x-input-label for="{{ $modalName }}-description" :value="__('Description')" />
        <textarea id="{{ $modalName }}-description" name="description" rows="2" class="ui-textarea mt-1 block w-full">{{ old('description', $structure?->description) }}</textarea>
        <x-input-error class="mt-2" :messages="$errors->get('description')" />
    </div>

    <label class="inline-flex items-center gap-2 text-sm text-gray-700">
        <input type="checkbox" name="is_active" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" @checked(old('is_active', $structure?->is_active ?? true)) />
        {{ __('Active') }}
    </label>

    <div>
        <h4 class="text-sm font-semibold text-gray-900">{{ __('Package components') }}</h4>
        <p class="mt-1 text-sm text-gray-500">{{ __('Toggle components on or off. Amounts come from each pay component’s default.') }}</p>
        <div class="mt-3 grid gap-3 sm:grid-cols-2">
            @foreach ($payComponents as $component)
                @php
                    $row = $assigned->get($component->id);
                    $enabled = (bool) old('components.'.$component->id.'.enabled', $row !== null);
                    $amountLabel = $component->calculation->value === 'percent_of_base'
                        ? number_format((float) $component->default_amount, 2).'%'
                        : number_format((float) $component->default_amount, 2);
                @endphp
                <label class="flex cursor-pointer items-center justify-between gap-3 rounded-lg border border-gray-200 p-3">
                    <span>
                        <span class="block text-sm font-medium text-gray-900">{{ $component->name }}</span>
                        <span class="mt-0.5 block text-xs text-gray-500">
                            {{ $component->type->label() }} · {{ $component->calculation->label() }} · {{ $amountLabel }}
                        </span>
                    </span>
                    <input
                        type="checkbox"
                        name="components[{{ $component->id }}][enabled]"
                        value="1"
                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                        @checked($enabled)
                    />
                </label>
            @endforeach
        </div>
    </div>

    <x-primary-button>{{ $structure ? __('Save structure') : __('Create structure') }}</x-primary-button>
</form>
