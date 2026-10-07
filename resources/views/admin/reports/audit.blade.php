<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-900">{{ __('Employee attendance audit') }}</h2>
                <p class="mt-1 text-sm text-gray-500">{{ __('Clock, QR, and network attempts — useful when employees report system issues.') }}</p>
            </div>
            <a href="{{ route('admin.reports.index') }}">
                <x-secondary-button type="button">{{ __('Back to reports') }}</x-secondary-button>
            </a>
        </div>
    </x-slot>

    <x-ui.page>
        <x-ui.card>
            <form method="GET" action="{{ route('admin.reports.audit') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-6">
                <input type="hidden" name="q" value="{{ request('q') }}">
                <input type="hidden" name="per_page" value="{{ request('per_page', 20) }}">

                <div>
                    <x-input-label for="from" :value="__('From')" />
                    <x-text-input id="from" name="from" type="date" class="mt-1 block w-full" :value="$from" />
                </div>
                <div>
                    <x-input-label for="to" :value="__('To')" />
                    <x-text-input id="to" name="to" type="date" class="mt-1 block w-full" :value="$to" />
                </div>
                <div>
                    <x-input-label for="employee_id" :value="__('Employee')" />
                    <x-ui.select id="employee_id" name="employee_id" class="mt-1">
                        <option value="">{{ __('All employees') }}</option>
                        @foreach ($employees as $employee)
                            <option value="{{ $employee->id }}" @selected((string) request('employee_id') === (string) $employee->id)>
                                {{ $employee->user?->name ?? $employee->employee_number }} ({{ $employee->employee_number }})
                            </option>
                        @endforeach
                    </x-ui.select>
                </div>
                <div>
                    <x-input-label for="action" :value="__('Action')" />
                    <x-ui.select id="action" name="action" class="mt-1">
                        <option value="">{{ __('All actions') }}</option>
                        @foreach ($actions as $action)
                            <option value="{{ $action->value }}" @selected(request('action') === $action->value)>{{ $action->label() }}</option>
                        @endforeach
                    </x-ui.select>
                </div>
                <div>
                    <x-input-label for="outcome" :value="__('Outcome')" />
                    <x-ui.select id="outcome" name="outcome" class="mt-1">
                        <option value="">{{ __('All outcomes') }}</option>
                        @foreach ($outcomes as $outcome)
                            <option value="{{ $outcome->value }}" @selected(request('outcome') === $outcome->value)>{{ $outcome->label() }}</option>
                        @endforeach
                    </x-ui.select>
                </div>
                <div>
                    <x-input-label for="network" :value="__('Network')" />
                    <x-ui.select id="network" name="network" class="mt-1">
                        <option value="">{{ __('Any network') }}</option>
                        <option value="allowed" @selected(request('network') === 'allowed')>{{ __('On allowlist') }}</option>
                        <option value="not_allowed" @selected(request('network') === 'not_allowed')>{{ __('Not on allowlist') }}</option>
                    </x-ui.select>
                </div>

                <div class="flex items-end gap-2 sm:col-span-2 lg:col-span-4 xl:col-span-6">
                    <x-primary-button>{{ __('Filter') }}</x-primary-button>
                    <a href="{{ route('admin.reports.audit') }}">
                        <x-secondary-button type="button">{{ __('Reset') }}</x-secondary-button>
                    </a>
                </div>
            </form>
        </x-ui.card>

        <x-ui.datatable :paginator="$logs" :search-placeholder="__('Search by employee, IP, or message…')">
            <table class="ui-table">
                <thead class="ui-thead">
                    <tr>
                        <th class="ui-th">{{ __('When') }}</th>
                        <th class="ui-th">{{ __('Employee') }}</th>
                        <th class="ui-th">{{ __('Action') }}</th>
                        <th class="ui-th">{{ __('Outcome') }}</th>
                        <th class="ui-th">{{ __('IP') }}</th>
                        <th class="ui-th">{{ __('Network') }}</th>
                        <th class="ui-th">{{ __('Details') }}</th>
                    </tr>
                </thead>
                <tbody class="ui-tbody">
                    @forelse ($logs as $log)
                        <tr class="ui-tr">
                            <td class="ui-td whitespace-nowrap">{{ $log->created_at?->format('Y-m-d H:i:s') }}</td>
                            <td class="ui-td-strong">
                                {{ $log->employee?->user?->name ?? $log->user?->name ?? '—' }}
                                @if ($log->employee?->employee_number)
                                    <div class="text-xs font-normal text-gray-500">{{ $log->employee->employee_number }}</div>
                                @endif
                            </td>
                            <td class="ui-td">{{ $log->action->label() }}</td>
                            <td class="ui-td">
                                <x-ui.badge :tone="$log->outcome->tone()">{{ $log->outcome->label() }}</x-ui.badge>
                            </td>
                            <td class="ui-td font-mono text-sm">{{ $log->ip_address ?? '—' }}</td>
                            <td class="ui-td">
                                <x-ui.badge :tone="$log->network_allowed ? 'green' : 'red'">
                                    {{ $log->network_allowed ? __('Allowlist') : __('Not allowlisted') }}
                                </x-ui.badge>
                                <div class="mt-1 text-xs text-gray-500">
                                    {{ $log->network_enforced ? __('Enforcement on') : __('Enforcement off') }}
                                </div>
                            </td>
                            <td class="ui-td">
                                <div>{{ $log->message ?? '—' }}</div>
                                @if (! empty($log->context['station']))
                                    <div class="mt-1 text-xs text-gray-500">{{ __('Station') }}: {{ $log->context['station'] }}</div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr class="ui-tr">
                            <td colspan="7" class="ui-td-empty">{{ __('No employee attendance audit events for this filter.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </x-ui.datatable>
    </x-ui.page>
</x-app-layout>
