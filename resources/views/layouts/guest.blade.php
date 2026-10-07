<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="{{ $companyBranding?->primary_color ?: '#4f46e5' }}">
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="default">
        <meta name="apple-mobile-web-app-title" content="{{ $companyBranding?->company_name ?: config('app.name', 'Laravel') }}">

        <title>{{ $companyBranding?->company_name ?: config('app.name', 'Laravel') }}</title>

        <link rel="manifest" href="{{ route('pwa.manifest') }}">
        <link rel="apple-touch-icon" href="{{ asset('icons/apple-touch-icon.png') }}">

        @if ($companyBranding?->faviconUrl())
            <link rel="icon" href="{{ $companyBranding->faviconUrl() }}" type="image/x-icon">
        @else
            <link rel="icon" href="{{ asset('icons/icon-192.png') }}" type="image/png">
        @endif

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @if ($companyBranding?->primary_color)
            <style>
                :root {
                    --color-brand: {{ $companyBranding->primary_color }};
                }
            </style>
        @endif
    </head>
    <body class="min-h-screen bg-slate-100 font-sans text-gray-900 antialiased">
        <div class="relative flex min-h-screen flex-col items-center justify-center px-4 py-10 sm:px-6">
            <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top,_rgba(99,102,241,0.12),_transparent_55%)]"></div>

            <div class="relative w-full max-w-md space-y-6">
                <div class="flex flex-col items-center gap-3 text-center">
                    <a href="/" class="inline-flex items-center justify-center rounded-2xl bg-white p-3 shadow-sm ring-1 ring-gray-200">
                        <x-application-logo class="h-12 w-12 fill-current text-gray-800" />
                    </a>
                    <div>
                        <p class="text-lg font-semibold text-gray-900">{{ $companyBranding?->company_name ?: config('app.name', 'Laravel') }}</p>
                        <p class="text-sm text-gray-500">{{ $companyBranding?->tagline ?: __('Attendance & HR') }}</p>
                    </div>
                </div>

                <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm sm:p-8">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
