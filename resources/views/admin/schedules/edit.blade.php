<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-900">{{ __('Edit work schedule') }}</h2>
    </x-slot>

    <x-ui.page width="sm">
        <x-ui.card>
            <form method="POST" action="{{ route('admin.schedules.update', $schedule) }}" class="space-y-6">
                @csrf
                @method('PUT')

                <div>
                    <x-input-label for="name" :value="__('Name')" />
                    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $schedule->name)" required autofocus />
                    <x-input-error class="mt-2" :messages="$errors->get('name')" />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="start_time" :value="__('Start time')" />
                        <x-text-input id="start_time" name="start_time" type="time" class="mt-1 block w-full" :value="old('start_time', $schedule->start_time)" required />
                        <x-input-error class="mt-2" :messages="$errors->get('start_time')" />
                    </div>
                    <div>
                        <x-input-label for="end_time" :value="__('End time')" />
                        <x-text-input id="end_time" name="end_time" type="time" class="mt-1 block w-full" :value="old('end_time', $schedule->end_time)" required />
                        <x-input-error class="mt-2" :messages="$errors->get('end_time')" />
                    </div>
                </div>

                <div>
                    <x-input-label :value="__('Work days')" />
                    @php
                        $days = [
                            1 => __('Monday'),
                            2 => __('Tuesday'),
                            3 => __('Wednesday'),
                            4 => __('Thursday'),
                            5 => __('Friday'),
                            6 => __('Saturday'),
                            7 => __('Sunday'),
                        ];
                        $selectedDays = old('work_days', $schedule->work_days ?? []);
                    @endphp
                    <div class="mt-2 grid grid-cols-2 gap-2">
                        @foreach ($days as $value => $label)
                            <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                <input type="checkbox" name="work_days[]" value="{{ $value }}" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" @checked(in_array($value, array_map('intval', (array) $selectedDays))) />
                                {{ $label }}
                            </label>
                        @endforeach
                    </div>
                    <x-input-error class="mt-2" :messages="$errors->get('work_days')" />
                </div>

                <div>
                    <x-input-label for="break_minutes" :value="__('Break (minutes)')" />
                    <x-text-input id="break_minutes" name="break_minutes" type="number" min="0" class="mt-1 block w-full" :value="old('break_minutes', $schedule->break_minutes)" />
                    <x-input-error class="mt-2" :messages="$errors->get('break_minutes')" />
                </div>

                <div class="flex items-center">
                    <input id="is_active" name="is_active" type="checkbox" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" @checked(old('is_active', $schedule->is_active)) />
                    <x-input-label for="is_active" :value="__('Active')" class="ms-2" />
                </div>

                <div class="flex items-center gap-4">
                    <x-primary-button>{{ __('Save') }}</x-primary-button>
                    <a href="{{ route('admin.schedules.index') }}"><x-secondary-button type="button">{{ __('Cancel') }}</x-secondary-button></a>
                </div>
            </form>
        </x-ui.card>
    </x-ui.page>
</x-app-layout>
