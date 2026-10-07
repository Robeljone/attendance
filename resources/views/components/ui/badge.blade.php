@props([
    'tone' => 'gray',
])

@php
    $tones = [
        'gray' => 'bg-gray-100 text-gray-700 ring-gray-200',
        'green' => 'bg-green-100 text-green-800 ring-green-200',
        'red' => 'bg-red-100 text-red-800 ring-red-200',
        'amber' => 'bg-amber-100 text-amber-800 ring-amber-200',
        'indigo' => 'bg-indigo-100 text-indigo-800 ring-indigo-200',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset '.$tones[$tone]]) }}>
    {{ $slot }}
</span>
