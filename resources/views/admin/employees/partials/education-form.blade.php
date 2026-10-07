@php
    /** @var \App\Models\EmployeeEducation|null $education */
    $prefix = $education ? 'edu-'.$education->id : 'edu-create';
    $modalName = $education ? 'edit-education-'.$education->id : 'add-education';
    $action = $education
        ? route('admin.employees.educations.update', [$employee, $education])
        : route('admin.employees.educations.store', $employee);
    $activeForm = old('form_modal') === $modalName;
    $field = function (string $key, mixed $default = null) use ($activeForm) {
        return $activeForm ? old($key, $default) : $default;
    };
    $currentLevel = $field(
        'level',
        $education?->level instanceof \BackedEnum ? $education->level->value : $education?->level
    );
@endphp

<form method="POST" action="{{ $action }}" class="space-y-5">
    @csrf
    @if ($education)
        @method('PUT')
    @endif
    <input type="hidden" name="form_modal" value="{{ $modalName }}">

    <div class="grid gap-5 sm:grid-cols-2">
        <div class="sm:col-span-2">
            <x-input-label for="{{ $prefix }}-institution" :value="__('Institution')" />
            <x-text-input id="{{ $prefix }}-institution" name="institution" type="text" class="mt-1 block w-full" :value="$field('institution', $education?->institution)" required />
            @if ($activeForm)<x-input-error class="mt-2" :messages="$errors->get('institution')" />@endif
        </div>

        <div>
            <x-input-label for="{{ $prefix }}-level" :value="__('Level')" />
            <x-ui.select id="{{ $prefix }}-level" name="level" required>
                <option value="">{{ __('Select level') }}</option>
                @foreach ($educationLevels ?? [] as $level)
                    <option value="{{ $level->value }}" @selected((string) $currentLevel === $level->value)>{{ $level->label() }}</option>
                @endforeach
            </x-ui.select>
            @if ($activeForm)<x-input-error class="mt-2" :messages="$errors->get('level')" />@endif
        </div>

        <div>
            <x-input-label for="{{ $prefix }}-field_of_study" :value="__('Field of study')" />
            <x-text-input id="{{ $prefix }}-field_of_study" name="field_of_study" type="text" class="mt-1 block w-full" :value="$field('field_of_study', $education?->field_of_study)" />
            @if ($activeForm)<x-input-error class="mt-2" :messages="$errors->get('field_of_study')" />@endif
        </div>

        <div class="sm:col-span-2">
            <x-input-label for="{{ $prefix }}-degree_title" :value="__('Degree / qualification title')" />
            <x-text-input id="{{ $prefix }}-degree_title" name="degree_title" type="text" class="mt-1 block w-full" :value="$field('degree_title', $education?->degree_title)" />
            @if ($activeForm)<x-input-error class="mt-2" :messages="$errors->get('degree_title')" />@endif
        </div>

        <div>
            <x-input-label for="{{ $prefix }}-start_year" :value="__('Start year')" />
            <x-text-input id="{{ $prefix }}-start_year" name="start_year" type="number" min="1950" class="mt-1 block w-full" :value="$field('start_year', $education?->start_year)" />
            @if ($activeForm)<x-input-error class="mt-2" :messages="$errors->get('start_year')" />@endif
        </div>

        <div>
            <x-input-label for="{{ $prefix }}-end_year" :value="__('End year')" />
            <x-text-input id="{{ $prefix }}-end_year" name="end_year" type="number" min="1950" class="mt-1 block w-full" :value="$field('end_year', $education?->end_year)" />
            @if ($activeForm)<x-input-error class="mt-2" :messages="$errors->get('end_year')" />@endif
        </div>

        <div>
            <x-input-label for="{{ $prefix }}-grade" :value="__('Grade / GPA')" />
            <x-text-input id="{{ $prefix }}-grade" name="grade" type="text" class="mt-1 block w-full" :value="$field('grade', $education?->grade)" />
            @if ($activeForm)<x-input-error class="mt-2" :messages="$errors->get('grade')" />@endif
        </div>

        <div class="flex items-end">
            <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                <input
                    type="checkbox"
                    name="is_highest"
                    value="1"
                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                    @checked((bool) $field('is_highest', $education?->is_highest))
                >
                {{ __('Highest qualification') }}
            </label>
        </div>

        <div class="sm:col-span-2">
            <x-input-label for="{{ $prefix }}-notes" :value="__('Notes')" />
            <textarea id="{{ $prefix }}-notes" name="notes" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ $field('notes', $education?->notes) }}</textarea>
            @if ($activeForm)<x-input-error class="mt-2" :messages="$errors->get('notes')" />@endif
        </div>
    </div>

    <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-4">
        <x-secondary-button type="button" x-on:click="$dispatch('close')">{{ __('Cancel') }}</x-secondary-button>
        <x-primary-button>{{ $education ? __('Save education') : __('Add education') }}</x-primary-button>
    </div>
</form>
