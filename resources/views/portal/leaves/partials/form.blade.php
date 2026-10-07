@php
    $activeForm = old('form_modal') === 'create-leave';
    $sickTypeIds = collect($leaveTypes ?? [])
        ->filter(fn ($type) => $type->requiresAttachment())
        ->pluck('id')
        ->map(fn ($id) => (string) $id)
        ->values()
        ->all();
    $selectedLeaveTypeId = $activeForm ? (string) old('leave_type_id', '') : '';
@endphp

<form
    method="POST"
    action="{{ route('portal.leaves.store') }}"
    enctype="multipart/form-data"
    class="space-y-5"
    x-data="{
        leaveTypeId: @js($selectedLeaveTypeId),
        sickIds: @js($sickTypeIds),
        get needsAttachment() {
            return this.sickIds.includes(String(this.leaveTypeId));
        }
    }"
>
    @csrf
    <input type="hidden" name="form_modal" value="create-leave">

    <div>
        <x-input-label for="create-leave-type" :value="__('Leave type')" />
        <x-ui.select id="create-leave-type" name="leave_type_id" x-model="leaveTypeId" required>
            <option value="">{{ __('Select type') }}</option>
            @foreach ($leaveTypes ?? [] as $type)
                <option value="{{ $type->id }}" @selected($selectedLeaveTypeId === (string) $type->id)>{{ $type->name }}</option>
            @endforeach
        </x-ui.select>
        @if ($activeForm)
            <x-input-error class="mt-2" :messages="$errors->get('leave_type_id')" />
        @endif
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <x-input-label for="create-leave-start" :value="__('Start date')" />
            <x-text-input id="create-leave-start" name="start_date" type="date" class="mt-1 block w-full" :value="$activeForm ? old('start_date') : ''" required />
            @if ($activeForm)
                <x-input-error class="mt-2" :messages="$errors->get('start_date')" />
            @endif
        </div>
        <div>
            <x-input-label for="create-leave-end" :value="__('End date')" />
            <x-text-input id="create-leave-end" name="end_date" type="date" class="mt-1 block w-full" :value="$activeForm ? old('end_date') : ''" required />
            @if ($activeForm)
                <x-input-error class="mt-2" :messages="$errors->get('end_date')" />
            @endif
        </div>
    </div>

    <div>
        <x-input-label for="create-leave-reason" :value="__('Reason')" />
        <textarea id="create-leave-reason" name="reason" rows="4" class="ui-textarea" required>{{ $activeForm ? old('reason') : '' }}</textarea>
        @if ($activeForm)
            <x-input-error class="mt-2" :messages="$errors->get('reason')" />
        @endif
    </div>

    <div x-show="needsAttachment" x-cloak>
        <x-input-label for="create-leave-attachment" :value="__('Medical attachment')" />
        <input
            id="create-leave-attachment"
            name="attachment"
            type="file"
            accept=".pdf,image/jpeg,image/png,image/webp"
            class="mt-1 block w-full text-sm text-gray-600 file:mr-3 file:rounded-md file:border-0 file:bg-gray-100 file:px-3 file:py-2 file:text-sm file:font-medium file:text-gray-700 hover:file:bg-gray-200"
            :required="needsAttachment"
        >
        <p class="mt-1 text-xs text-gray-500">{{ __('Required for sick leave. PDF or image up to 5MB.') }}</p>
        @if ($activeForm)
            <x-input-error class="mt-2" :messages="$errors->get('attachment')" />
        @endif
    </div>

    <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-4">
        <x-secondary-button type="button" x-on:click="$dispatch('close')">{{ __('Cancel') }}</x-secondary-button>
        <x-primary-button>{{ __('Submit request') }}</x-primary-button>
    </div>
</form>
