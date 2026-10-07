<x-guest-layout>
    @php
        $isStaffPortal = ($portal ?? 'employee') === 'staff';
        $formAction = $isStaffPortal ? route('admin.login.store') : route('login.store');
        $heading = $isStaffPortal ? __('Staff sign in') : __('Employee sign in');
        $subtitle = $isStaffPortal
            ? __('HR, manager, and admin accounts')
            : __('Employee portal access');
    @endphp

    <div class="mb-6 text-center">
        <h1 class="text-xl font-semibold text-gray-900">{{ $heading }}</h1>
        <p class="mt-1 text-sm text-gray-500">{{ $subtitle }}</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ $formAction }}" class="space-y-6">
        @csrf

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="mt-1 block w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="mt-1 block w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div>
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-end gap-3">
            @if (Route::has('password.request'))
                <a class="text-sm text-gray-600 underline transition hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <x-primary-button>
                {{ __('Log in') }}
            </x-primary-button>
        </div>
    </form>

    <p class="mt-6 text-center text-sm text-gray-500">
        @if ($isStaffPortal)
            <a href="{{ route('login') }}" class="font-medium text-indigo-600 underline hover:text-indigo-500">
                {{ __('Employee login') }}
            </a>
        @else
            <a href="{{ route('admin.login') }}" class="font-medium text-indigo-600 underline hover:text-indigo-500">
                {{ __('Staff login') }}
            </a>
        @endif
    </p>
</x-guest-layout>
