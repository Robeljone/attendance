<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-900">{{ __('Dashboard') }}</h2>
        </div>
    </x-slot>

    <x-ui.page>
        <x-ui.flash />

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($stats ?? [] as $label => $value)
                <div class="ui-stat">
                    <p class="ui-stat-label">{{ is_string($label) ? $label : ($value['label'] ?? '') }}</p>
                    <p class="ui-stat-value">
                        {{ is_array($value) ? ($value['value'] ?? '') : $value }}
                    </p>
                </div>
            @endforeach
        </div>

        <x-ui.card>
            <h3 class="ui-card-title">{{ __('Quick links') }}</h3>

            @if (Auth::user()->canManageHr())
                <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    <a href="{{ route('admin.employees.index') }}" class="ui-quick-link">{{ __('Employees') }}</a>
                    <a href="{{ route('admin.departments.index') }}" class="ui-quick-link">{{ __('Departments') }}</a>
                    <a href="{{ route('admin.schedules.index') }}" class="ui-quick-link">{{ __('Work schedules') }}</a>
                    <a href="{{ route('admin.attendance.index') }}" class="ui-quick-link">{{ __('Attendance') }}</a>
                    <a href="{{ route('admin.leaves.index') }}" class="ui-quick-link">{{ __('Leave requests') }}</a>
                    <a href="{{ route('admin.payroll.index') }}" class="ui-quick-link">{{ __('Payroll') }}</a>
                    <a href="{{ route('admin.reports.index') }}" class="ui-quick-link">{{ __('Reports') }}</a>
                    <a href="{{ route('admin.qr.station') }}" class="ui-quick-link">{{ __('QR station') }}</a>
                    <a href="{{ route('admin.settings.edit') }}" class="ui-quick-link">{{ __('Settings') }}</a>
                    @if (Auth::user()->canManageBranding())
                        <a href="{{ route('admin.branding.edit') }}" class="ui-quick-link">{{ __('Branding') }}</a>
                    @endif
                </div>
            @else
                <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <a href="{{ route('portal.attendance.index') }}" class="ui-quick-link">{{ __('My attendance') }}</a>
                    <a href="{{ route('portal.leaves.index') }}" class="ui-quick-link">{{ __('My leaves') }}</a>
                    <a href="{{ route('portal.payslips.index') }}" class="ui-quick-link">{{ __('My payslips') }}</a>
                    <a href="{{ route('portal.profile.show') }}" class="ui-quick-link">{{ __('My info') }}</a>
                </div>
            @endif
        </x-ui.card>

        <x-ui.card>
            <p class="text-sm text-gray-600">{{ __('Welcome back, :name.', ['name' => Auth::user()->name]) }}</p>
        </x-ui.card>
    </x-ui.page>
</x-app-layout>
