<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-900">{{ __('Create payroll run') }}</h2>
    </x-slot>

    <x-ui.page width="sm">
        <x-ui.card>
            <form method="POST" action="{{ route('admin.payroll.store') }}" class="space-y-6">
                @csrf

                <div>
                    <x-input-label for="name" :value="__('Period name')" />
                    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name')" placeholder="{{ __('e.g. March 2026') }}" required />
                    <x-input-error class="mt-2" :messages="$errors->get('name')" />
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

                <div class="flex items-center gap-4">
                    <x-primary-button>{{ __('Generate payroll') }}</x-primary-button>
                    <a href="{{ route('admin.payroll.index') }}"><x-secondary-button type="button">{{ __('Cancel') }}</x-secondary-button></a>
                </div>
            </form>
        </x-ui.card>
    </x-ui.page>
</x-app-layout>
