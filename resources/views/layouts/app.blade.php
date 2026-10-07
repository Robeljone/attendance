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
        <div
            x-data="{
                sidebarOpen: false,
                sidebarCollapsed: false,
                toggleSidebar() {
                    if (window.innerWidth >= 1024) {
                        this.sidebarCollapsed = ! this.sidebarCollapsed;
                    } else {
                        this.sidebarOpen = ! this.sidebarOpen;
                    }
                },
                closeSidebarOnMobile() {
                    if (window.innerWidth < 1024) {
                        this.sidebarOpen = false;
                    }
                },
            }"
            class="min-h-screen"
        >
            @include('layouts.navigation')

            <div
                class="flex min-h-screen flex-col transition-all duration-200 ease-in-out lg:pl-64"
                :class="{ 'lg:!pl-0': sidebarCollapsed }"
            >
                <header class="sticky top-0 z-30 flex h-16 shrink-0 items-center gap-x-4 border-b border-gray-200/80 bg-white/90 px-4 backdrop-blur sm:gap-x-6 sm:px-6 lg:px-8">
                    <button
                        type="button"
                        @click="toggleSidebar()"
                        class="inline-flex items-center justify-center rounded-lg p-2 text-gray-500 transition hover:bg-gray-100 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                        :aria-expanded="sidebarOpen || ! sidebarCollapsed"
                        aria-controls="app-sidebar"
                    >
                        <span class="sr-only">{{ __('Toggle sidebar') }}</span>
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>

                    <div class="hidden h-6 w-px bg-gray-200 sm:block lg:hidden" aria-hidden="true"></div>

                    <div class="flex flex-1 items-center justify-end gap-x-4">
                        <x-dropdown align="right" width="48">
                            <x-slot name="trigger">
                                <button class="inline-flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium text-gray-600 transition hover:bg-gray-50 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-100 text-xs font-semibold text-indigo-700">
                                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                    </span>
                                    <span class="hidden sm:inline">{{ Auth::user()->name }}</span>
                                    <svg class="h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </x-slot>

                            <x-slot name="content">
                                <x-dropdown-link :href="route('profile.edit')">
                                    {{ __('Profile') }}
                                </x-dropdown-link>

                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <x-dropdown-link :href="route('logout')"
                                        onclick="event.preventDefault(); this.closest('form').submit();">
                                        {{ __('Log Out') }}
                                    </x-dropdown-link>
                                </form>
                            </x-slot>
                        </x-dropdown>
                    </div>
                </header>

                @isset($header)
                    <div class="border-b border-gray-200 bg-white">
                        <div class="w-full px-4 py-5 sm:px-6 lg:px-8">
                            {{ $header }}
                        </div>
                    </div>
                @endisset

                <main class="flex-1">
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
