<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-900">{{ __('Reports') }}</h2>
    </x-slot>

    <x-ui.page>
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
