@php
    $prefix = 'doc-create';
    $modalName = 'add-document';
    $activeForm = old('form_modal') === $modalName;
    $field = function (string $key, mixed $default = null) use ($activeForm) {
        return $activeForm ? old($key, $default) : $default;
    };
    $currentType = $field('type');
@endphp

<form method="POST" action="{{ route('admin.employees.documents.store', $employee) }}" enctype="multipart/form-data" class="space-y-5">
    @csrf
    <input type="hidden" name="form_modal" value="{{ $modalName }}">

    <div class="grid gap-5 sm:grid-cols-2">
        <div>
            <x-input-label for="{{ $prefix }}-type" :value="__('Document type')" />
            <x-ui.select id="{{ $prefix }}-type" name="type" required>
                <option value="">{{ __('Select type') }}</option>
                @foreach ($documentTypes ?? [] as $type)
                    <option value="{{ $type->value }}" @selected((string) $currentType === $type->value)>{{ $type->label() }}</option>
                @endforeach
            </x-ui.select>
            @if ($activeForm)<x-input-error class="mt-2" :messages="$errors->get('type')" />@endif
        </div>

        <div>
            <x-input-label for="{{ $prefix }}-title" :value="__('Title')" />
            <x-text-input id="{{ $prefix }}-title" name="title" type="text" class="mt-1 block w-full" :value="$field('title')" required />
            @if ($activeForm)<x-input-error class="mt-2" :messages="$errors->get('title')" />@endif
        </div>

        <div class="sm:col-span-2">
            <x-input-label for="{{ $prefix }}-document" :value="__('Scanned file')" />
            <input id="{{ $prefix }}-document" name="document" type="file" accept=".pdf,image/jpeg,image/png,image/webp" required class="mt-1 block w-full text-sm text-gray-600 file:mr-3 file:rounded-md file:border-0 file:bg-gray-100 file:px-3 file:py-2 file:text-sm file:font-medium file:text-gray-700 hover:file:bg-gray-200">
            <p class="mt-1 text-xs text-gray-500">{{ __('PDF or image up to 5MB.') }}</p>
            @if ($activeForm)<x-input-error class="mt-2" :messages="$errors->get('document')" />@endif
        </div>

        <div>
            <x-input-label for="{{ $prefix }}-document_number" :value="__('Document number')" />
            <x-text-input id="{{ $prefix }}-document_number" name="document_number" type="text" class="mt-1 block w-full" :value="$field('document_number')" />
            @if ($activeForm)<x-input-error class="mt-2" :messages="$errors->get('document_number')" />@endif
        </div>

        <div>
            <x-input-label for="{{ $prefix }}-issued_on" :value="__('Issued on')" />
            <x-text-input id="{{ $prefix }}-issued_on" name="issued_on" type="date" class="mt-1 block w-full" :value="$field('issued_on')" />
            @if ($activeForm)<x-input-error class="mt-2" :messages="$errors->get('issued_on')" />@endif
        </div>

        <div>
            <x-input-label for="{{ $prefix }}-expires_on" :value="__('Expires on')" />
            <x-text-input id="{{ $prefix }}-expires_on" name="expires_on" type="date" class="mt-1 block w-full" :value="$field('expires_on')" />
            @if ($activeForm)<x-input-error class="mt-2" :messages="$errors->get('expires_on')" />@endif
        </div>

        <div class="sm:col-span-2">
            <x-input-label for="{{ $prefix }}-notes" :value="__('Notes')" />
            <textarea id="{{ $prefix }}-notes" name="notes" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ $field('notes') }}</textarea>
            @if ($activeForm)<x-input-error class="mt-2" :messages="$errors->get('notes')" />@endif
        </div>
    </div>

    <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-4">
        <x-secondary-button type="button" x-on:click="$dispatch('close')">{{ __('Cancel') }}</x-secondary-button>
        <x-primary-button>{{ __('Upload document') }}</x-primary-button>
    </div>
</form>
