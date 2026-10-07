@props(['active'])

@php
$classes = ($active ?? false)
    ? 'flex w-full items-center rounded-lg bg-indigo-50 px-3 py-2 text-sm font-semibold text-indigo-700 transition'
    : 'flex w-full items-center rounded-lg px-3 py-2 text-sm font-medium text-gray-600 transition hover:bg-gray-50 hover:text-gray-900';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
