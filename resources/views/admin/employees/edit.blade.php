<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-900">{{ __('Edit employee') }}</h2>
                <p class="mt-1 text-sm text-gray-500">{{ $employee->user?->name }} · {{ $employee->employee_number }}</p>
            </div>
            <a href="{{ route('admin.employees.show', $employee) }}">
                <x-secondary-button type="button">{{ __('Back to profile') }}</x-secondary-button>
            </a>
        </div>
    </x-slot>

    <x-ui.page>
        <x-ui.flash />

        <x-ui.card>
            @include('admin.employees.partials.form', [
                'employee' => $employee,
                'departments' => $departments,
                'schedules' => $schedules,
                'statuses' => $statuses,
                'genders' => $genders,
                'maritalStatuses' => $maritalStatuses,
                'cancelUrl' => route('admin.employees.show', $employee),
            ])
        </x-ui.card>
    </x-ui.page>
</x-app-layout>
