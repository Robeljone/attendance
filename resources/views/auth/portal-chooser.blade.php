<x-guest-layout>
    <div class="space-y-6">
        <div class="text-center">
            <h1 class="text-xl font-semibold text-gray-900">{{ __('Choose your portal') }}</h1>
            <p class="mt-1 text-sm text-gray-500">{{ __('Sign in with the login page that matches your role.') }}</p>
        </div>

        <div class="grid gap-3">
            <a
                href="{{ route('login') }}"
                class="block rounded-xl border border-gray-200 bg-slate-50 px-4 py-4 text-left transition hover:border-indigo-300 hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
            >
                <span class="block text-sm font-semibold text-gray-900">{{ __('Employee portal') }}</span>
                <span class="mt-1 block text-sm text-gray-500">{{ __('Clock in/out, leave, payslips, and your profile.') }}</span>
            </a>

            <a
                href="{{ route('admin.login') }}"
                class="block rounded-xl border border-gray-200 bg-slate-50 px-4 py-4 text-left transition hover:border-indigo-300 hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
            >
                <span class="block text-sm font-semibold text-gray-900">{{ __('Staff portal') }}</span>
                <span class="mt-1 block text-sm text-gray-500">{{ __('For HR, managers, and admins.') }}</span>
            </a>
        </div>
    </div>
</x-guest-layout>
