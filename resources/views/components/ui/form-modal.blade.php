@props([
    'name',
    'title',
    'show' => false,
    'maxWidth' => 'lg',
])

<x-modal :name="$name" :show="$show" :max-width="$maxWidth" focusable>
    <div class="flex min-h-0 flex-1 flex-col">
        <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
            <h2 class="text-lg font-semibold text-gray-900">{{ $title }}</h2>
            <button
                type="button"
                class="rounded-md p-1 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600"
                x-on:click="$dispatch('close')"
                aria-label="{{ __('Close') }}"
            >
                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div class="min-h-0 flex-1 overflow-y-auto px-6 py-5">
            {{ $slot }}
        </div>
    </div>
</x-modal>
