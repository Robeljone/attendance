@php
    // HR users get the admin sidebar everywhere except when browsing the employee portal.
    $isAdminSidebar = Auth::user()->canManageHr() && ! request()->routeIs('portal.*');
@endphp

<!-- Mobile sidebar backdrop -->
<div
    x-show="sidebarOpen"
    x-transition:enter="transition-opacity ease-linear duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition-opacity ease-linear duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    @click="sidebarOpen = false"
    class="fixed inset-0 z-40 bg-slate-900/40 backdrop-blur-sm lg:hidden"
    x-cloak
></div>

<!-- Sidebar -->
<aside
    id="app-sidebar"
    class="fixed inset-y-0 left-0 z-50 flex w-64 -translate-x-full flex-col border-r border-gray-200 bg-white shadow-sm transition-transform duration-200 ease-in-out lg:z-40 lg:translate-x-0"
    :class="{
        'translate-x-0': sidebarOpen,
        '-translate-x-full': ! sidebarOpen,
        'lg:translate-x-0': ! sidebarCollapsed,
        'lg:-translate-x-full': sidebarCollapsed,
    }"
>
    <div class="flex h-16 shrink-0 items-center justify-between gap-x-3 border-b border-gray-100 px-4">
        <a href="{{ route('dashboard') }}" class="flex items-center" @click="closeSidebarOnMobile()">
            <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
        </a>

        <button
            type="button"
            @click="sidebarOpen = false"
            class="inline-flex items-center justify-center rounded-md p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 lg:hidden"
        >
            <span class="sr-only">{{ __('Close sidebar') }}</span>
            <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <nav class="flex-1 space-y-5 overflow-y-auto px-3 py-4">
        @if ($isAdminSidebar && Auth::user()->canManageHr())
            <div>
                <div class="mb-2 px-3 text-[11px] font-semibold uppercase tracking-wider text-gray-400">
                    {{ __('HR Admin') }}
                </div>

                <div class="space-y-2">
                    <x-sidebar.nav-link
                        :href="route('dashboard')"
                        :active="request()->routeIs('dashboard')"
                        icon="home"
                        x-on:click="closeSidebarOnMobile()"
                    >
                        {{ __('Dashboard') }}
                    </x-sidebar.nav-link>

                    <x-sidebar.nav-group
                        :title="__('People')"
                        icon="users"
                        :active="request()->routeIs('admin.employees.*', 'admin.departments.*')"
                    >
                        <x-sidebar.nav-link
                            :href="route('admin.employees.index')"
                            :active="request()->routeIs('admin.employees.*')"
                            icon="user"
                            :nested="true"
                            x-on:click="closeSidebarOnMobile()"
                        >
                            {{ __('Employees') }}
                        </x-sidebar.nav-link>
                        <x-sidebar.nav-link
                            :href="route('admin.departments.index')"
                            :active="request()->routeIs('admin.departments.*')"
                            icon="building"
                            :nested="true"
                            x-on:click="closeSidebarOnMobile()"
                        >
                            {{ __('Departments') }}
                        </x-sidebar.nav-link>
                    </x-sidebar.nav-group>

                    <x-sidebar.nav-group
                        :title="__('Time & attendance')"
                        icon="clock"
                        :active="request()->routeIs('admin.schedules.*', 'admin.attendance.*', 'admin.leaves.*', 'admin.qr.*')"
                    >
                        <x-sidebar.nav-link
                            :href="route('admin.schedules.index')"
                            :active="request()->routeIs('admin.schedules.*')"
                            icon="calendar"
                            :nested="true"
                            x-on:click="closeSidebarOnMobile()"
                        >
                            {{ __('Schedules') }}
                        </x-sidebar.nav-link>
                        <x-sidebar.nav-link
                            :href="route('admin.attendance.index')"
                            :active="request()->routeIs('admin.attendance.*')"
                            icon="clock"
                            :nested="true"
                            x-on:click="closeSidebarOnMobile()"
                        >
                            {{ __('Attendance') }}
                        </x-sidebar.nav-link>
                        <x-sidebar.nav-link
                            :href="route('admin.leaves.index')"
                            :active="request()->routeIs('admin.leaves.*')"
                            icon="briefcase"
                            :nested="true"
                            x-on:click="closeSidebarOnMobile()"
                        >
                            {{ __('Leaves') }}
                        </x-sidebar.nav-link>
                        <x-sidebar.nav-link
                            :href="route('admin.qr.station')"
                            :active="request()->routeIs('admin.qr.*')"
                            icon="qr-code"
                            :nested="true"
                            x-on:click="closeSidebarOnMobile()"
                        >
                            {{ __('QR Station') }}
                        </x-sidebar.nav-link>
                    </x-sidebar.nav-group>

                    <x-sidebar.nav-group
                        :title="__('Pay & reports')"
                        icon="banknotes"
                        :active="request()->routeIs('admin.payroll.*', 'admin.pay-components.*', 'admin.reports.*')"
                    >
                        <x-sidebar.nav-link
                            :href="route('admin.payroll.index')"
                            :active="request()->routeIs('admin.payroll.*')"
                            icon="banknotes"
                            :nested="true"
                            x-on:click="closeSidebarOnMobile()"
                        >
                            {{ __('Payroll') }}
                        </x-sidebar.nav-link>
                        <x-sidebar.nav-link
                            :href="route('admin.pay-components.index')"
                            :active="request()->routeIs('admin.pay-components.*')"
                            icon="banknotes"
                            :nested="true"
                            x-on:click="closeSidebarOnMobile()"
                        >
                            {{ __('Pay components') }}
                        </x-sidebar.nav-link>
                        <x-sidebar.nav-link
                            :href="route('admin.reports.index')"
                            :active="request()->routeIs('admin.reports.index')"
                            icon="chart"
                            :nested="true"
                            x-on:click="closeSidebarOnMobile()"
                        >
                            {{ __('Reports') }}
                        </x-sidebar.nav-link>
                        <x-sidebar.nav-link
                            :href="route('admin.reports.audit')"
                            :active="request()->routeIs('admin.reports.audit')"
                            icon="chart"
                            :nested="true"
                            x-on:click="closeSidebarOnMobile()"
                        >
                            {{ __('Attendance audit') }}
                        </x-sidebar.nav-link>
                    </x-sidebar.nav-group>

                    <x-sidebar.nav-group
                        :title="__('System')"
                        icon="cog"
                        :active="request()->routeIs('admin.settings.*', 'admin.branding.*')"
                    >
                        <x-sidebar.nav-link
                            :href="route('admin.settings.edit')"
                            :active="request()->routeIs('admin.settings.*')"
                            icon="cog"
                            :nested="true"
                            x-on:click="closeSidebarOnMobile()"
                        >
                            {{ __('Settings') }}
                        </x-sidebar.nav-link>
                        @if (Auth::user()->canManageBranding())
                            <x-sidebar.nav-link
                                :href="route('admin.branding.edit')"
                                :active="request()->routeIs('admin.branding.*')"
                                icon="swatch"
                                :nested="true"
                                x-on:click="closeSidebarOnMobile()"
                            >
                                {{ __('Branding') }}
                            </x-sidebar.nav-link>
                        @endif
                    </x-sidebar.nav-group>
                </div>
            </div>
        @else
            <div>
                <div class="mb-2 px-3 text-[11px] font-semibold uppercase tracking-wider text-gray-400">
                    {{ __('Employee portal') }}
                </div>

                <div class="space-y-2">
                    <x-sidebar.nav-link
                        :href="route('dashboard')"
                        :active="request()->routeIs('dashboard')"
                        icon="home"
                        x-on:click="closeSidebarOnMobile()"
                    >
                        {{ __('Dashboard') }}
                    </x-sidebar.nav-link>

                    <x-sidebar.nav-group
                        :title="__('My work')"
                        icon="briefcase"
                        :active="request()->routeIs('portal.attendance.*', 'portal.leaves.*', 'portal.payslips.*')"
                    >
                        <x-sidebar.nav-link
                            :href="route('portal.attendance.index')"
                            :active="request()->routeIs('portal.attendance.*')"
                            icon="clock"
                            :nested="true"
                            x-on:click="closeSidebarOnMobile()"
                        >
                            {{ __('My Attendance') }}
                        </x-sidebar.nav-link>
                        <x-sidebar.nav-link
                            :href="route('portal.leaves.index')"
                            :active="request()->routeIs('portal.leaves.*')"
                            icon="calendar"
                            :nested="true"
                            x-on:click="closeSidebarOnMobile()"
                        >
                            {{ __('My Leaves') }}
                        </x-sidebar.nav-link>
                        <x-sidebar.nav-link
                            :href="route('portal.payslips.index')"
                            :active="request()->routeIs('portal.payslips.*')"
                            icon="document"
                            :nested="true"
                            x-on:click="closeSidebarOnMobile()"
                        >
                            {{ __('My Payslips') }}
                        </x-sidebar.nav-link>
                    </x-sidebar.nav-group>

                    <x-sidebar.nav-group
                        :title="__('Account')"
                        icon="user"
                        :active="request()->routeIs('portal.profile.*')"
                    >
                        <x-sidebar.nav-link
                            :href="route('portal.profile.show')"
                            :active="request()->routeIs('portal.profile.*')"
                            icon="user"
                            :nested="true"
                            x-on:click="closeSidebarOnMobile()"
                        >
                            {{ __('My Info') }}
                        </x-sidebar.nav-link>
                    </x-sidebar.nav-group>
                </div>
            </div>
        @endif
    </nav>

    @if (Auth::user()->canManageHr())
        <div class="shrink-0 border-t border-gray-100 p-3">
            @if ($isAdminSidebar)
                <a
                    href="{{ route('portal.attendance.index') }}"
                    class="flex w-full items-center justify-center gap-2 rounded-md border border-gray-200 bg-gray-50 px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-100 hover:text-gray-900"
                    x-on:click="closeSidebarOnMobile()"
                >
                    <x-sidebar.icon name="user" class="h-4 w-4 text-gray-500" />
                    {{ __('Employee Portal') }}
                </a>
            @else
                <a
                    href="{{ route('admin.employees.index') }}"
                    class="flex w-full items-center justify-center gap-2 rounded-md border border-indigo-200 bg-indigo-50 px-3 py-2 text-sm font-medium text-indigo-700 transition hover:bg-indigo-100"
                    x-on:click="closeSidebarOnMobile()"
                >
                    <x-sidebar.icon name="cog" class="h-4 w-4 text-indigo-600" />
                    {{ __('HR Admin') }}
                </a>
            @endif
        </div>
    @endif

    <div class="shrink-0 border-t border-gray-100 px-4 py-4 lg:hidden">
        <div class="text-sm font-medium text-gray-800">{{ Auth::user()->name }}</div>
        <div class="text-xs text-gray-500">{{ Auth::user()->email }}</div>
    </div>
</aside>
