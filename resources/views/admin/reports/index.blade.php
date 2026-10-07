<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-900">{{ __('Reports') }}</h2>
            <a href="{{ route('admin.reports.audit') }}">
                <x-primary-button type="button">{{ __('Employee attendance audit') }}</x-primary-button>
            </a>
        </div>
    </x-slot>

    <x-ui.page>
        <x-ui.card>
            <form method="GET" action="{{ route('admin.reports.index') }}" class="flex flex-col flex-wrap items-end gap-4 sm:flex-row">
                <div>
                    <x-input-label for="from" :value="__('From date')" />
                    <x-text-input id="from" name="from" type="date" class="mt-1 block w-full" :value="$from" />
                </div>
                <div>
                    <x-input-label for="to" :value="__('To date')" />
                    <x-text-input id="to" name="to" type="date" class="mt-1 block w-full" :value="$to" />
                </div>
                <div class="flex gap-2">
                    <x-primary-button>{{ __('Filter') }}</x-primary-button>
                    <a href="{{ route('admin.reports.index') }}"><x-secondary-button type="button">{{ __('Reset') }}</x-secondary-button></a>
                </div>
            </form>
        </x-ui.card>

        <x-ui.card>
            <h3 class="ui-card-title">{{ __('HR attendance summary') }}</h3>
            @if (! empty($attendanceSummary))
                <dl class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($attendanceSummary as $key => $item)
                        <div class="ui-stat">
                            <dt class="ui-stat-label">{{ is_array($item) ? ($item['label'] ?? $key) : $key }}</dt>
                            <dd class="ui-stat-value">{{ is_array($item) ? ($item['value'] ?? '') : $item }}</dd>
                        </div>
                    @endforeach
                </dl>
            @else
                <p class="mt-4 text-sm text-gray-500">{{ __('No attendance summary data available.') }}</p>
            @endif
        </x-ui.card>

        <x-ui.card>
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <h3 class="ui-card-title">{{ __('Employee attendance audit') }}</h3>
                <a href="{{ route('admin.reports.audit', ['from' => $from, 'to' => $to]) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                    {{ __('Open full audit log') }}
                </a>
            </div>
            @if (! empty($auditSummary))
                <dl class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($auditSummary as $key => $item)
                        <div class="ui-stat">
                            <dt class="ui-stat-label">{{ $key }}</dt>
                            <dd class="ui-stat-value">{{ $item }}</dd>
                        </div>
                    @endforeach
                </dl>
            @endif
            <p class="mt-4 text-sm text-gray-500">
                {{ __('Includes successful and failed clock/QR attempts, plus blocked off-network requests.') }}
            </p>
        </x-ui.card>

        <x-ui.card>
            <h3 class="ui-card-title">{{ __('Finance payroll summary') }}</h3>
            @if (! empty($payrollSummary))
                <dl class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($payrollSummary as $key => $item)
                        <div class="ui-stat">
                            <dt class="ui-stat-label">{{ is_array($item) ? ($item['label'] ?? $key) : $key }}</dt>
                            <dd class="ui-stat-value">{{ is_array($item) ? ($item['value'] ?? '') : $item }}</dd>
                        </div>
                    @endforeach
                </dl>
            @else
                <p class="mt-4 text-sm text-gray-500">{{ __('No payroll summary data available.') }}</p>
            @endif
        </x-ui.card>
    </x-ui.page>
</x-app-layout>
