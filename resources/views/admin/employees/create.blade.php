<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-900">{{ __('Add employee') }}</h2>
            <a href="{{ route('admin.employees.index') }}">
                <x-secondary-button type="button">{{ __('Back to employees') }}</x-secondary-button>
            </a>
        </div>
    </x-slot>

    <x-ui.page>
        <x-ui.flash />
        <x-ui.card>
            @include('admin.employees.partials.form', [
                'employee' => null,
                'departments' => $departments,
                'schedules' => $schedules,
                'statuses' => $statuses,
                'genders' => $genders,
                'maritalStatuses' => $maritalStatuses,
                'cancelUrl' => route('admin.employees.index'),
            ])
        </x-ui.card>
    </x-ui.page>
</x-app-layout>
