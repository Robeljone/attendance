@php
    $empStatus = $employee->status;
    $empStatusLabel = $empStatus instanceof \App\Enums\EmploymentStatus
        ? $empStatus->label()
        : ($empStatus instanceof \BackedEnum ? $empStatus->value : ($empStatus ?? '—'));
    $empStatusTone = match ($empStatus instanceof \BackedEnum ? $empStatus->value : (string) $empStatus) {
        'active' => 'green',
        'on_leave' => 'amber',
        'terminated' => 'red',
        default => 'gray',
    };
    $openModal = old('form_modal');
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-4">
                @if ($employee->photoUrl())
                    <img src="{{ $employee->photoUrl() }}" alt="{{ $employee->user?->name }}" class="h-14 w-14 rounded-full object-cover ring-1 ring-gray-200">
                @else
                    <div class="flex h-14 w-14 items-center justify-center rounded-full bg-gray-100 text-lg font-semibold text-gray-500 ring-1 ring-gray-200">
                        {{ strtoupper(substr($employee->user?->name ?? 'E', 0, 1)) }}
                    </div>
                @endif
                <div>
                    <h2 class="text-xl font-semibold leading-tight text-gray-900">{{ $employee->user?->name ?? __('Employee') }}</h2>
                    <p class="mt-1 text-sm text-gray-500">{{ $employee->position ?? __('No position') }} · {{ $employee->employee_number }}</p>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.employees.edit', $employee) }}">
                    <x-primary-button type="button">{{ __('Edit') }}</x-primary-button>
                </a>
                <form method="POST" action="{{ route('admin.employees.destroy', $employee) }}" onsubmit="return confirm('{{ __('Delete this employee?') }}');">
                    @csrf
                    @method('DELETE')
                    <x-danger-button>{{ __('Delete') }}</x-danger-button>
                </form>
            </div>
        </div>
    </x-slot>

    <x-ui.page>
        <x-ui.flash />

        <x-ui.card>
            <h3 class="ui-card-title">{{ __('Job details') }}</h3>
            <dl class="mt-4 grid grid-cols-1 gap-x-6 gap-y-4 text-sm sm:grid-cols-2 lg:grid-cols-3">
                <div>
                    <dt class="font-medium text-gray-500">{{ __('Work email') }}</dt>
                    <dd class="mt-1 text-gray-900">{{ $employee->user?->email ?? '—' }}</dd>
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
                    <dt class="font-medium text-gray-500">{{ __('Phone') }}</dt>
                    <dd class="mt-1 text-gray-900">{{ $employee->phone ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="font-medium text-gray-500">{{ __('Hire date') }}</dt>
                    <dd class="mt-1 text-gray-900">{{ $employee->hire_date?->format('M j, Y') ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="font-medium text-gray-500">{{ __('Base salary') }}</dt>
                    <dd class="mt-1 text-gray-900">{{ $employee->base_salary !== null ? number_format((float) $employee->base_salary, 2) : '—' }}</dd>
                </div>
                <div>
                    <dt class="font-medium text-gray-500">{{ __('Bank account') }}</dt>
                    <dd class="mt-1 text-gray-900">{{ $employee->bank_account ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="font-medium text-gray-500">{{ __('Status') }}</dt>
                    <dd class="mt-1">
                        <x-ui.badge :tone="$empStatusTone">{{ $empStatusLabel }}</x-ui.badge>
                    </dd>
                </div>
                <div>
                    <dt class="font-medium text-gray-500">{{ __('Role') }}</dt>
                    <dd class="mt-1 text-gray-900">{{ $employee->user?->role instanceof \App\Enums\UserRole ? $employee->user->role->label() : ($employee->user?->role ?? '—') }}</dd>
                </div>
            </dl>
        </x-ui.card>

        <x-ui.card>
            <h3 class="ui-card-title">{{ __('Personal information') }}</h3>
            <dl class="mt-4 grid grid-cols-1 gap-x-6 gap-y-4 text-sm sm:grid-cols-2 lg:grid-cols-3">
                <div>
                    <dt class="font-medium text-gray-500">{{ __('Date of birth') }}</dt>
                    <dd class="mt-1 text-gray-900">{{ $employee->date_of_birth?->format('M j, Y') ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="font-medium text-gray-500">{{ __('Gender') }}</dt>
                    <dd class="mt-1 text-gray-900">{{ $employee->gender?->label() ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="font-medium text-gray-500">{{ __('Marital status') }}</dt>
                    <dd class="mt-1 text-gray-900">{{ $employee->marital_status?->label() ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="font-medium text-gray-500">{{ __('Nationality') }}</dt>
                    <dd class="mt-1 text-gray-900">{{ $employee->nationality ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="font-medium text-gray-500">{{ __('National ID') }}</dt>
                    <dd class="mt-1 text-gray-900">{{ $employee->national_id ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="font-medium text-gray-500">{{ __('Tax ID') }}</dt>
                    <dd class="mt-1 text-gray-900">{{ $employee->tax_id ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="font-medium text-gray-500">{{ __('Personal email') }}</dt>
                    <dd class="mt-1 text-gray-900">{{ $employee->personal_email ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="font-medium text-gray-500">{{ __('Blood group') }}</dt>
                    <dd class="mt-1 text-gray-900">{{ $employee->blood_group ?? '—' }}</dd>
                </div>
                <div class="sm:col-span-2 lg:col-span-3">
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
                @if ($employee->notes)
                    <div class="sm:col-span-2 lg:col-span-3">
                        <dt class="font-medium text-gray-500">{{ __('Notes') }}</dt>
                        <dd class="mt-1 whitespace-pre-wrap text-gray-900">{{ $employee->notes }}</dd>
                    </div>
                @endif
            </dl>
        </x-ui.card>

        <x-ui.card flush>
            <x-slot name="header">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <h3 class="ui-card-title">{{ __('Education') }}</h3>
                    <x-primary-button type="button" x-data x-on:click="$dispatch('open-modal', 'add-education')">
                        {{ __('Add education') }}
                    </x-primary-button>
                </div>
            </x-slot>
            <div class="ui-table-wrap">
                <table class="ui-table">
                    <thead class="ui-thead">
                        <tr>
                            <th class="ui-th">{{ __('Institution') }}</th>
                            <th class="ui-th">{{ __('Level') }}</th>
                            <th class="ui-th">{{ __('Field') }}</th>
                            <th class="ui-th">{{ __('Years') }}</th>
                            <th class="ui-th">{{ __('Grade') }}</th>
                            <th class="ui-th-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="ui-tbody">
                        @forelse ($employee->educations as $education)
                            <tr class="ui-tr">
                                <td class="ui-td-strong">
                                    {{ $education->institution }}
                                    @if ($education->is_highest)
                                        <x-ui.badge tone="green" class="ml-2">{{ __('Highest') }}</x-ui.badge>
                                    @endif
                                    @if ($education->degree_title)
                                        <div class="mt-0.5 text-xs font-normal text-gray-500">{{ $education->degree_title }}</div>
                                    @endif
                                </td>
                                <td class="ui-td">{{ $education->level?->label() ?? '—' }}</td>
                                <td class="ui-td">{{ $education->field_of_study ?? '—' }}</td>
                                <td class="ui-td">
                                    {{ $education->start_year ?? '—' }}
                                    –
                                    {{ $education->end_year ?? '—' }}
                                </td>
                                <td class="ui-td">{{ $education->grade ?? '—' }}</td>
                                <td class="ui-td-right">
                                    <div class="ui-actions">
                                        <button type="button" class="ui-action" x-data x-on:click="$dispatch('open-modal', 'edit-education-{{ $education->id }}')">
                                            <span>{{ __('Edit') }}</span>
                                        </button>
                                        <form method="POST" action="{{ route('admin.employees.educations.destroy', [$employee, $education]) }}" onsubmit="return confirm('{{ __('Remove this education record?') }}');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="ui-action text-red-600 hover:text-red-700">{{ __('Remove') }}</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr class="ui-tr">
                                <td colspan="6" class="ui-td-empty">{{ __('No education records yet.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.card>

        <x-ui.card flush>
            <x-slot name="header">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <h3 class="ui-card-title">{{ __('Documents') }}</h3>
                    <x-primary-button type="button" x-data x-on:click="$dispatch('open-modal', 'add-document')">
                        {{ __('Upload document') }}
                    </x-primary-button>
                </div>
            </x-slot>
            <div class="ui-table-wrap">
                <table class="ui-table">
                    <thead class="ui-thead">
                        <tr>
                            <th class="ui-th">{{ __('Title') }}</th>
                            <th class="ui-th">{{ __('Type') }}</th>
                            <th class="ui-th">{{ __('Number') }}</th>
                            <th class="ui-th">{{ __('Issued') }}</th>
                            <th class="ui-th">{{ __('Expires') }}</th>
                            <th class="ui-th-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="ui-tbody">
                        @forelse ($employee->documents as $document)
                            <tr class="ui-tr">
                                <td class="ui-td-strong">
                                    {{ $document->title }}
                                    @if ($document->original_name)
                                        <div class="mt-0.5 text-xs font-normal text-gray-500">{{ $document->original_name }}</div>
                                    @endif
                                </td>
                                <td class="ui-td">{{ $document->type?->label() ?? '—' }}</td>
                                <td class="ui-td">{{ $document->document_number ?? '—' }}</td>
                                <td class="ui-td">{{ $document->issued_on?->format('M j, Y') ?? '—' }}</td>
                                <td class="ui-td">
                                    @if ($document->expires_on)
                                        <span @class(['text-red-600' => $document->isExpired()])>
                                            {{ $document->expires_on->format('M j, Y') }}
                                            @if ($document->isExpired())
                                                ({{ __('Expired') }})
                                            @endif
                                        </span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="ui-td-right">
                                    <div class="ui-actions">
                                        <a class="ui-action" href="{{ route('admin.employees.documents.download', [$employee, $document]) }}">{{ __('Download') }}</a>
                                        <form method="POST" action="{{ route('admin.employees.documents.destroy', [$employee, $document]) }}" onsubmit="return confirm('{{ __('Remove this document?') }}');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="ui-action text-red-600 hover:text-red-700">{{ __('Remove') }}</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr class="ui-tr">
                                <td colspan="6" class="ui-td-empty">{{ __('No documents uploaded yet.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.card>

        <x-ui.card flush>
            <x-slot name="header">
                <h3 class="ui-card-title">{{ __('Recent attendance') }}</h3>
            </x-slot>
            <div class="ui-table-wrap">
                <table class="ui-table">
                    <thead class="ui-thead">
                        <tr>
                            <th class="ui-th">{{ __('Date') }}</th>
                            <th class="ui-th">{{ __('Clock in') }}</th>
                            <th class="ui-th">{{ __('Clock out') }}</th>
                            <th class="ui-th">{{ __('Worked') }}</th>
                        </tr>
                    </thead>
                    <tbody class="ui-tbody">
                        @forelse ($attendance ?? [] as $record)
                            <tr class="ui-tr">
                                <td class="ui-td-strong">{{ $record->work_date?->format('M j, Y') ?? '—' }}</td>
                                <td class="ui-td">{{ $record->clock_in_at?->format('H:i') ?? '—' }}</td>
                                <td class="ui-td">{{ $record->clock_out_at?->format('H:i') ?? '—' }}</td>
                                <td class="ui-td">
                                    @if ($record->worked_minutes)
                                        {{ floor($record->worked_minutes / 60) }}h {{ $record->worked_minutes % 60 }}m
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr class="ui-tr">
                                <td colspan="4" class="ui-td-empty">{{ __('No attendance records yet.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.card>

        <x-ui.form-modal
            name="add-education"
            :title="__('Add education')"
            :show="$openModal === 'add-education'"
            max-width="2xl"
        >
            @include('admin.employees.partials.education-form', [
                'employee' => $employee,
                'education' => null,
                'educationLevels' => $educationLevels,
            ])
        </x-ui.form-modal>

        @foreach ($employee->educations as $education)
            <x-ui.form-modal
                name="edit-education-{{ $education->id }}"
                :title="__('Edit education')"
                :show="$openModal === 'edit-education-'.$education->id"
                max-width="2xl"
            >
                @include('admin.employees.partials.education-form', [
                    'employee' => $employee,
                    'education' => $education,
                    'educationLevels' => $educationLevels,
                ])
            </x-ui.form-modal>
        @endforeach

        <x-ui.form-modal
            name="add-document"
            :title="__('Upload document')"
            :show="$openModal === 'add-document'"
            max-width="2xl"
        >
            @include('admin.employees.partials.document-form', [
                'employee' => $employee,
                'documentTypes' => $documentTypes,
            ])
        </x-ui.form-modal>
    </x-ui.page>
</x-app-layout>
