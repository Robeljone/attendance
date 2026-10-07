@php
    $activeForm = old('form_modal') === 'create-payroll';
    $selectedExcluded = collect($activeForm ? old('excluded_employee_ids', []) : [])->map(fn ($id) => (int) $id);
@endphp

<form method="POST" action="{{ route('admin.payroll.store') }}" class="space-y-5">
    @csrf
    <input type="hidden" name="form_modal" value="create-payroll">

    <div>
        <x-input-label for="create-payroll-name" :value="__('Period name')" />
        <x-text-input id="create-payroll-name" name="name" type="text" class="mt-1 block w-full" :value="$activeForm ? old('name') : ''" placeholder="{{ __('e.g. March 2026') }}" required />
        @if ($activeForm)
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        @endif
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <x-input-label for="create-payroll-start_date" :value="__('Start date')" />
            <x-text-input id="create-payroll-start_date" name="start_date" type="date" class="mt-1 block w-full" :value="$activeForm ? old('start_date') : ''" required />
            @if ($activeForm)
                <x-input-error class="mt-2" :messages="$errors->get('start_date')" />
            @endif
        </div>
        <div>
            <x-input-label for="create-payroll-end_date" :value="__('End date')" />
            <x-text-input id="create-payroll-end_date" name="end_date" type="date" class="mt-1 block w-full" :value="$activeForm ? old('end_date') : ''" required />
            @if ($activeForm)
                <x-input-error class="mt-2" :messages="$errors->get('end_date')" />
            @endif
        </div>
    </div>

    @if (($employees ?? collect())->isNotEmpty())
        <div>
            <x-input-label :value="__('Exclude employees (optional)')" />
            <div class="mt-2 max-h-40 space-y-2 overflow-y-auto rounded-lg border border-gray-200 p-3">
                @foreach ($employees as $employee)
                    <label class="flex items-center gap-2 text-sm text-gray-700">
                        <input
                            type="checkbox"
                            name="excluded_employee_ids[]"
                            value="{{ $employee->id }}"
                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                            @checked($selectedExcluded->contains($employee->id))
                        />
                        <span>{{ $employee->user?->name ?? $employee->employee_number }} ({{ $employee->employee_number }})</span>
                    </label>
                @endforeach
            </div>
            <p class="mt-1 text-xs text-gray-500">{{ __('Checked employees will not be included in this payroll run.') }}</p>
        </div>
    @endif

    <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-4">
        <x-secondary-button type="button" x-on:click="$dispatch('close')">{{ __('Cancel') }}</x-secondary-button>
        <x-primary-button>{{ __('Generate payroll') }}</x-primary-button>
    </div>
</form>
