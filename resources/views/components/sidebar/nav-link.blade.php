@props([
    'href',
    'active' => false,
    'icon' => null,
    'nested' => false,
])

@php
    $classes = ($active ?? false)
        ? 'group flex w-full items-center gap-3 rounded-lg bg-indigo-50 px-3 py-2 text-sm font-semibold text-indigo-700 transition'
        : 'group flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-gray-600 transition hover:bg-gray-50 hover:text-gray-900';

    if ($nested) {
        $classes .= ' pl-3';
    }

    $iconClasses = ($active ?? false)
        ? 'text-indigo-600'
        : 'text-gray-400 group-hover:text-gray-600';
@endphp

<a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
    @if ($icon)
        <x-sidebar.icon :name="$icon" @class([$iconClasses]) />
    @endif
    <span class="truncate">{{ $slot }}</span>
</a>
