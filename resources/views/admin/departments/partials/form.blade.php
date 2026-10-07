@php
    /** @var \App\Models\Department|null $department */
    $prefix = $prefix ?? ($department ? 'edit-'.$department->id : 'create');
    $modalName = $department ? 'edit-department-'.$department->id : 'create-department';
    $action = $department
        ? route('admin.departments.update', $department)
        : route('admin.departments.store');
    $activeBag = old('form_modal') === $modalName;
    $nameValue = $activeBag ? old('name', $department?->name) : $department?->name;
    $codeValue = $activeBag ? old('code', $department?->code) : $department?->code;
    $descriptionValue = $activeBag ? old('description', $department?->description) : $department?->description;
    $isActiveValue = $activeBag ? (bool) old('is_active', false) : ($department?->is_active ?? true);
@endphp

<form method="POST" action="{{ $action }}" class="space-y-5">
    @csrf
    @if ($department)
        @method('PUT')
    @endif
    <input type="hidden" name="form_modal" value="{{ $modalName }}">

    <div>
        <x-input-label for="{{ $prefix }}-name" :value="__('Name')" />
        <x-text-input id="{{ $prefix }}-name" name="name" type="text" class="mt-1 block w-full" :value="$nameValue" required />
        @if ($activeBag)
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        @endif
    </div>

    <div>
        <x-input-label for="{{ $prefix }}-code" :value="__('Code')" />
        <x-text-input id="{{ $prefix }}-code" name="code" type="text" class="mt-1 block w-full" :value="$codeValue" required />
        @if ($activeBag)
            <x-input-error class="mt-2" :messages="$errors->get('code')" />
        @endif
    </div>

    <div>
        <x-input-label for="{{ $prefix }}-description" :value="__('Description')" />
        <textarea id="{{ $prefix }}-description" name="description" rows="3" class="ui-textarea">{{ $descriptionValue }}</textarea>
        @if ($activeBag)
            <x-input-error class="mt-2" :messages="$errors->get('description')" />
        @endif
    </div>

    <div class="flex items-center">
        <input
            id="{{ $prefix }}-is_active"
            name="is_active"
            type="checkbox"
            value="1"
            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
            @checked($isActiveValue)
        />
        <x-input-label for="{{ $prefix }}-is_active" :value="__('Active')" class="ms-2" />
    </div>

    <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-4">
        <x-secondary-button type="button" x-on:click="$dispatch('close')">{{ __('Cancel') }}</x-secondary-button>
        <x-primary-button>{{ $department ? __('Save') : __('Create') }}</x-primary-button>
    </div>
</form>
