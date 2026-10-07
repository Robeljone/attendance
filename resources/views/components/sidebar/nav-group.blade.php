@props([
    'title',
    'icon' => null,
    'active' => false,
    'open' => null,
])

@php
    $startsOpen = $open ?? $active;
@endphp

<div
    x-data="{ open: @js($startsOpen) }"
    class="space-y-1"
>
    <button
        type="button"
        class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-left text-sm font-semibold text-gray-700 transition hover:bg-gray-50 hover:text-gray-900"
        :class="{ 'bg-gray-50 text-gray-900': open }"
        @click="open = ! open"
        :aria-expanded="open.toString()"
    >
        @if ($icon)
            <span class="shrink-0 text-gray-400" :class="{ 'text-indigo-600': open }">
                <x-sidebar.icon :name="$icon" />
            </span>
        @endif
        <span class="truncate">{{ $title }}</span>
        <svg
            class="ml-auto h-4 w-4 shrink-0 text-gray-400 transition-transform duration-200"
            :class="{ 'rotate-180': open }"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
            stroke-width="1.5"
            stroke="currentColor"
            aria-hidden="true"
        >
            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
        </svg>
    </button>

    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 -translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-1"
        class="ml-4 space-y-1 border-l border-gray-200 pl-2"
        @if (! $startsOpen) x-cloak @endif
    >
        {{ $slot }}
    </div>
</div>
