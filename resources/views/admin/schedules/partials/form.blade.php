@php
    /** @var \App\Models\WorkSchedule|null $schedule */
    $prefix = $prefix ?? ($schedule ? 'edit-'.$schedule->id : 'create');
    $modalName = $schedule ? 'edit-schedule-'.$schedule->id : 'create-schedule';
    $action = $schedule
        ? route('admin.schedules.update', $schedule)
        : route('admin.schedules.store');
    $activeBag = old('form_modal') === $modalName;
    $days = [
        1 => __('Monday'),
        2 => __('Tuesday'),
        3 => __('Wednesday'),
        4 => __('Thursday'),
        5 => __('Friday'),
        6 => __('Saturday'),
        7 => __('Sunday'),
    ];
    $selectedDays = $activeBag ? old('work_days', $schedule?->work_days ?? [1, 2, 3, 4, 5]) : ($schedule?->work_days ?? [1, 2, 3, 4, 5]);
    $startTime = $activeBag ? old('start_time', $schedule?->start_time ?? '09:00') : ($schedule?->start_time ?? '09:00');
    $endTime = $activeBag ? old('end_time', $schedule?->end_time ?? '17:00') : ($schedule?->end_time ?? '17:00');
    if (is_string($startTime) && strlen($startTime) > 5) {
        $startTime = substr($startTime, 0, 5);
    }
    if (is_string($endTime) && strlen($endTime) > 5) {
        $endTime = substr($endTime, 0, 5);
    }
    $nameValue = $activeBag ? old('name', $schedule?->name) : $schedule?->name;
    $breakValue = $activeBag ? old('break_minutes', $schedule?->break_minutes ?? 60) : ($schedule?->break_minutes ?? 60);
    $isActiveValue = $activeBag ? (bool) old('is_active', false) : ($schedule?->is_active ?? true);
@endphp

<form method="POST" action="{{ $action }}" class="space-y-5">
    @csrf
    @if ($schedule)
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

    <div class="grid grid-cols-2 gap-4">
        <div>
            <x-input-label for="{{ $prefix }}-start_time" :value="__('Start time')" />
            <x-text-input id="{{ $prefix }}-start_time" name="start_time" type="time" class="mt-1 block w-full" :value="$startTime" required />
            @if ($activeBag)
                <x-input-error class="mt-2" :messages="$errors->get('start_time')" />
            @endif
        </div>
        <div>
            <x-input-label for="{{ $prefix }}-end_time" :value="__('End time')" />
            <x-text-input id="{{ $prefix }}-end_time" name="end_time" type="time" class="mt-1 block w-full" :value="$endTime" required />
            @if ($activeBag)
                <x-input-error class="mt-2" :messages="$errors->get('end_time')" />
            @endif
        </div>
    </div>

    <div>
        <x-input-label :value="__('Work days')" />
        <div class="mt-2 grid grid-cols-2 gap-2">
            @foreach ($days as $value => $label)
                <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                    <input
                        type="checkbox"
                        name="work_days[]"
                        value="{{ $value }}"
                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                        @checked(in_array($value, array_map('intval', (array) $selectedDays), true))
                    />
                    {{ $label }}
                </label>
            @endforeach
        </div>
        @if ($activeBag)
            <x-input-error class="mt-2" :messages="$errors->get('work_days')" />
        @endif
    </div>

    <div>
        <x-input-label for="{{ $prefix }}-break_minutes" :value="__('Break (minutes)')" />
        <x-text-input id="{{ $prefix }}-break_minutes" name="break_minutes" type="number" min="0" class="mt-1 block w-full" :value="$breakValue" />
        @if ($activeBag)
            <x-input-error class="mt-2" :messages="$errors->get('break_minutes')" />
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
        <x-primary-button>{{ $schedule ? __('Save') : __('Create') }}</x-primary-button>
    </div>
</form>
