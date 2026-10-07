@php
    $activeBag = old('form_modal') === 'create-payroll';
@endphp

<form method="POST" action="{{ route('admin.payroll.store') }}" class="space-y-5">
    @csrf
    <input type="hidden" name="form_modal" value="create-payroll">

    <div>
        <x-input-label for="create-payroll-name" :value="__('Period name')" />
        <x-text-input id="create-payroll-name" name="name" type="text" class="mt-1 block w-full" :value="$activeBag ? old('name') : ''" placeholder="{{ __('e.g. March 2026') }}" required />
        @if ($activeBag)
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        @endif
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <x-input-label for="create-payroll-start_date" :value="__('Start date')" />
            <x-text-input id="create-payroll-start_date" name="start_date" type="date" class="mt-1 block w-full" :value="$activeBag ? old('start_date') : ''" required />
            @if ($activeBag)
                <x-input-error class="mt-2" :messages="$errors->get('start_date')" />
            @endif
        </div>
        <div>
            <x-input-label for="create-payroll-end_date" :value="__('End date')" />
            <x-text-input id="create-payroll-end_date" name="end_date" type="date" class="mt-1 block w-full" :value="$activeBag ? old('end_date') : ''" required />
            @if ($activeBag)
                <x-input-error class="mt-2" :messages="$errors->get('end_date')" />
            @endif
        </div>
    </div>

    <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-4">
        <x-secondary-button type="button" x-on:click="$dispatch('close')">{{ __('Cancel') }}</x-secondary-button>
        <x-primary-button>{{ __('Generate payroll') }}</x-primary-button>
    </div>
</form>
