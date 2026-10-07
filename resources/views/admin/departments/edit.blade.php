<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-900">{{ __('Edit department') }}</h2>
    </x-slot>

    <x-ui.page width="sm">
        <x-ui.card>
            <form method="POST" action="{{ route('admin.departments.update', $department) }}" class="space-y-6">
                @csrf
                @method('PUT')

                <div>
                    <x-input-label for="name" :value="__('Name')" />
                    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $department->name)" required autofocus />
                    <x-input-error class="mt-2" :messages="$errors->get('name')" />
                </div>

                <div>
                    <x-input-label for="code" :value="__('Code')" />
                    <x-text-input id="code" name="code" type="text" class="mt-1 block w-full" :value="old('code', $department->code)" />
                    <x-input-error class="mt-2" :messages="$errors->get('code')" />
                </div>

                <div>
                    <x-input-label for="description" :value="__('Description')" />
                    <textarea id="description" name="description" rows="3" class="ui-textarea">{{ old('description', $department->description) }}</textarea>
                    <x-input-error class="mt-2" :messages="$errors->get('description')" />
                </div>

                <div class="flex items-center">
                    <input id="is_active" name="is_active" type="checkbox" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" @checked(old('is_active', $department->is_active)) />
                    <x-input-label for="is_active" :value="__('Active')" class="ms-2" />
                </div>

                <div class="flex items-center gap-4">
                    <x-primary-button>{{ __('Save') }}</x-primary-button>
                    <a href="{{ route('admin.departments.index') }}"><x-secondary-button type="button">{{ __('Cancel') }}</x-secondary-button></a>
                </div>
            </form>
        </x-ui.card>
    </x-ui.page>
</x-app-layout>
