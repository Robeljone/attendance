@props(['disabled' => false])

<select @disabled($disabled) {{ $attributes->merge(['class' => 'ui-select']) }}>
    {{ $slot }}
</select>
