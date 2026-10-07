@props([
    'padding' => true,
    'flush' => false,
])

<div {{ $attributes->merge(['class' => 'ui-card']) }}>
    @isset($header)
        <div class="ui-card-header">
            {{ $header }}
        </div>
    @endisset

    @if ($flush)
        {{ $slot }}
    @else
        <div @class(['ui-card-body' => $padding])>
            {{ $slot }}
        </div>
    @endif
</div>
