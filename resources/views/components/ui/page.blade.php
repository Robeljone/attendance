@props([
    'width' => 'default',
])

@php
    $inner = match ($width) {
        'sm' => 'ui-page-inner-sm',
        'md' => 'ui-page-inner-md',
        default => 'ui-page-inner',
    };
@endphp

<div {{ $attributes->merge(['class' => 'ui-page']) }}>
    <div class="{{ $inner }}">
        {{ $slot }}
    </div>
</div>
