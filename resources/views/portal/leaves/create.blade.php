<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-900">{{ __('Request leave') }}</h2>
    </x-slot>

    <x-ui.page>
        <x-ui.card>
            <form method="POST" action="{{ route('portal.leaves.store') }}" class="space-y-6">
                @csrf

                <div>
                    <x-input-label for="leave_type_id" :value="__('Leave type')" />
                    <x-ui.select id="leave_type_id" name="leave_type_id" required>
                        <option value="">{{ __('Select type') }}</option>
                        @foreach ($leaveTypes ?? [] as $type)
                            <option value="{{ $type->id }}" @selected(old('leave_type_id') == $type->id)>{{ $type->name }}</option>
                        @endforeach
                    </x-ui.select>
                    <x-input-error class="mt-2" :messages="$errors->get('leave_type_id')" />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="start_date" :value="__('Start date')" />
                        <x-text-input id="start_date" name="start_date" type="date" class="mt-1 block w-full" :value="old('start_date')" required />
                        <x-input-error class="mt-2" :messages="$errors->get('start_date')" />
                    </div>
                    <div>
                        <x-input-label for="end_date" :value="__('End date')" />
                        <x-text-input id="end_date" name="end_date" type="date" class="mt-1 block w-full" :value="old('end_date')" required />
                        <x-input-error class="mt-2" :messages="$errors->get('end_date')" />
                    </div>
                </div>

                <div>
                    <x-input-label for="reason" :value="__('Reason')" />
                    <textarea id="reason" name="reason" rows="4" class="ui-textarea" required>{{ old('reason') }}</textarea>
                    <x-input-error class="mt-2" :messages="$errors->get('reason')" />
                </div>

                <div class="flex items-center gap-4">
                    <x-primary-button>{{ __('Submit request') }}</x-primary-button>
                    <a href="{{ route('portal.leaves.index') }}"><x-secondary-button type="button">{{ __('Cancel') }}</x-secondary-button></a>
                </div>
            </form>
        </x-ui.card>
    </x-ui.page>
</x-app-layout>
