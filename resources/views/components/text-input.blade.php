@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500']) }}>
