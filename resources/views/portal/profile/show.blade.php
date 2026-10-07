<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-900">{{ __('My info') }}</h2>
    </x-slot>

    <x-ui.page>
        @php
            $employee = $employee ?? Auth::user()->employee;
            $empStatus = $employee?->status;
            $empStatusLabel = $empStatus instanceof \App\Enums\EmploymentStatus
                ? $empStatus->label()
                : ($empStatus instanceof \BackedEnum ? $empStatus->value : ($empStatus ?? '—'));
            $empStatusTone = match ($empStatus instanceof \BackedEnum ? $empStatus->value : (string) $empStatus) {
                'active' => 'green',
                'on_leave' => 'amber',
                'terminated' => 'red',
                default => 'gray',
            };
        @endphp

        @if ($employee)
            <x-ui.card>
                <div class="flex items-center gap-4">
                    @if ($employee->photoUrl())
                        <img src="{{ $employee->photoUrl() }}" alt="{{ Auth::user()->name }}" class="h-16 w-16 rounded-full object-cover ring-1 ring-gray-200">
                    @else
                        <div class="flex h-16 w-16 items-center justify-center rounded-full bg-gray-100 text-xl font-semibold text-gray-500 ring-1 ring-gray-200">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </div>
                    @endif
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">{{ Auth::user()->name }}</h3>
                        <p class="text-sm text-gray-500">{{ $employee->position ?? __('Employee') }} · {{ $employee->employee_number }}</p>
                    </div>
                </div>
            </x-ui.card>
        @endif

        <x-ui.card>
            <h3 class="ui-card-title">{{ __('Personal') }}</h3>
            <dl class="mt-4 grid grid-cols-1 gap-x-6 gap-y-4 text-sm sm:grid-cols-2">
                <div>
                    <dt class="font-medium text-gray-500">{{ __('Name') }}</dt>
                    <dd class="mt-1 text-gray-900">{{ Auth::user()->name }}</dd>
                </div>
                <div>
                    <dt class="font-medium text-gray-500">{{ __('Work email') }}</dt>
                    <dd class="mt-1 text-gray-900">{{ Auth::user()->email }}</dd>
                </div>
                @if ($employee)
                    <div>
                        <dt class="font-medium text-gray-500">{{ __('Employee number') }}</dt>
                        <dd class="mt-1 text-gray-900">{{ $employee->employee_number }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-gray-500">{{ __('Phone') }}</dt>
                        <dd class="mt-1 text-gray-900">{{ $employee->phone ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-gray-500">{{ __('Department') }}</dt>
                        <dd class="mt-1 text-gray-900">{{ $employee->department?->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-gray-500">{{ __('Position') }}</dt>
                        <dd class="mt-1 text-gray-900">{{ $employee->position ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-gray-500">{{ __('Hire date') }}</dt>
                        <dd class="mt-1 text-gray-900">{{ $employee->hire_date?->format('M j, Y') ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-gray-500">{{ __('Status') }}</dt>
                        <dd class="mt-1">
                            <x-ui.badge :tone="$empStatusTone">{{ $empStatusLabel }}</x-ui.badge>
                        </dd>
                    </div>
                    <div>
                        <dt class="font-medium text-gray-500">{{ __('Date of birth') }}</dt>
                        <dd class="mt-1 text-gray-900">{{ $employee->date_of_birth?->format('M j, Y') ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-gray-500">{{ __('Gender') }}</dt>
                        <dd class="mt-1 text-gray-900">{{ $employee->gender?->label() ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-gray-500">{{ __('Nationality') }}</dt>
                        <dd class="mt-1 text-gray-900">{{ $employee->nationality ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-gray-500">{{ __('Blood group') }}</dt>
                        <dd class="mt-1 text-gray-900">{{ $employee->blood_group ?? '—' }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="font-medium text-gray-500">{{ __('Address') }}</dt>
                        <dd class="mt-1 text-gray-900">{{ $employee->address ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-gray-500">{{ __('Emergency contact') }}</dt>
                        <dd class="mt-1 text-gray-900">{{ $employee->emergency_contact_name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-gray-500">{{ __('Emergency phone') }}</dt>
                        <dd class="mt-1 text-gray-900">{{ $employee->emergency_contact_phone ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-gray-500">{{ __('Relationship') }}</dt>
                        <dd class="mt-1 text-gray-900">{{ $employee->emergency_contact_relationship ?? '—' }}</dd>
                    </div>
                @else
                    <div class="sm:col-span-2 text-sm text-gray-500">{{ __('No employee profile linked to your account.') }}</div>
                @endif
            </dl>
        </x-ui.card>

        @if ($employee)
            <x-ui.card flush>
                <x-slot name="header">
                    <h3 class="ui-card-title">{{ __('Education') }}</h3>
                </x-slot>
                <div class="ui-table-wrap">
                    <table class="ui-table">
                        <thead class="ui-thead">
                            <tr>
                                <th class="ui-th">{{ __('Institution') }}</th>
                                <th class="ui-th">{{ __('Level') }}</th>
                                <th class="ui-th">{{ __('Field') }}</th>
                                <th class="ui-th">{{ __('Years') }}</th>
                            </tr>
                        </thead>
                        <tbody class="ui-tbody">
                            @forelse ($employee->educations as $education)
                                <tr class="ui-tr">
                                    <td class="ui-td-strong">
                                        {{ $education->institution }}
                                        @if ($education->is_highest)
                                            <span class="ml-2 text-xs font-medium text-green-700">{{ __('Highest') }}</span>
                                        @endif
                                    </td>
                                    <td class="ui-td">{{ $education->level?->label() ?? '—' }}</td>
                                    <td class="ui-td">{{ $education->field_of_study ?? '—' }}</td>
                                    <td class="ui-td">{{ $education->start_year ?? '—' }} – {{ $education->end_year ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr class="ui-tr">
                                    <td colspan="4" class="ui-td-empty">{{ __('No education records on file.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-ui.card>

            <x-ui.card flush>
                <x-slot name="header">
                    <h3 class="ui-card-title">{{ __('Documents on file') }}</h3>
                </x-slot>
                <div class="ui-table-wrap">
                    <table class="ui-table">
                        <thead class="ui-thead">
                            <tr>
                                <th class="ui-th">{{ __('Title') }}</th>
                                <th class="ui-th">{{ __('Type') }}</th>
                                <th class="ui-th">{{ __('Expires') }}</th>
                            </tr>
                        </thead>
                        <tbody class="ui-tbody">
                            @forelse ($employee->documents as $document)
                                <tr class="ui-tr">
                                    <td class="ui-td-strong">{{ $document->title }}</td>
                                    <td class="ui-td">{{ $document->type?->label() ?? '—' }}</td>
                                    <td class="ui-td">{{ $document->expires_on?->format('M j, Y') ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr class="ui-tr">
                                    <td colspan="3" class="ui-td-empty">{{ __('No documents on file.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-ui.card>
        @endif
    </x-ui.page>
</x-app-layout>
