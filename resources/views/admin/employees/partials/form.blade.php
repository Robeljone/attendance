@php
    /** @var \App\Models\Employee|null $employee */
    $prefix = $prefix ?? ($employee ? 'edit-'.$employee->id : 'create');
    $action = $employee
        ? route('admin.employees.update', $employee)
        : route('admin.employees.store');
    $cancelUrl = $cancelUrl ?? ($employee
        ? route('admin.employees.show', $employee)
        : route('admin.employees.index'));
    $field = fn (string $key, mixed $default = null) => old($key, $default);
    $currentRole = $field(
        'role',
        $employee?->user?->role instanceof \BackedEnum
            ? $employee->user->role->value
            : ($employee?->user?->role ?? 'employee')
    );
    $selectedSchedule = $field(
        'work_schedule_id',
        $employee?->workSchedules?->first()?->id
    );
    $selectedDepartment = $field('department_id', $employee?->department_id);
    $hireDate = $field('hire_date', $employee?->hire_date?->format('Y-m-d'));
    $terminationDate = $field('termination_date', $employee?->termination_date?->format('Y-m-d'));
    $dateOfBirth = $field('date_of_birth', $employee?->date_of_birth?->format('Y-m-d'));
    $assignedComponents = $employee?->payComponents?->keyBy('id') ?? collect();
    $currentGender = $field(
        'gender',
        $employee?->gender instanceof \BackedEnum ? $employee->gender->value : $employee?->gender
    );
    $currentMarital = $field(
        'marital_status',
        $employee?->marital_status instanceof \BackedEnum ? $employee->marital_status->value : $employee?->marital_status
    );
@endphp

<form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="space-y-6">
    @csrf
    @if ($employee)
        @method('PUT')
    @endif

    <div>
        <h3 class="text-sm font-semibold text-gray-900">{{ __('Account & job') }}</h3>
        <div class="mt-3 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            <div class="sm:col-span-2 lg:col-span-3">
                <x-input-label for="{{ $prefix }}-name" :value="__('Name')" />
                <x-text-input id="{{ $prefix }}-name" name="name" type="text" class="mt-1 block w-full" :value="$field('name', $employee?->user?->name)" required autofocus />
                <x-input-error class="mt-2" :messages="$errors->get('name')" />
            </div>

            <div>
                <x-input-label for="{{ $prefix }}-email" :value="__('Work email')" />
                <x-text-input id="{{ $prefix }}-email" name="email" type="email" class="mt-1 block w-full" :value="$field('email', $employee?->user?->email)" required />
                <x-input-error class="mt-2" :messages="$errors->get('email')" />
            </div>

            <div>
                <x-input-label for="{{ $prefix }}-password" :value="__('Password')" />
                @if ($employee)
                    <x-text-input id="{{ $prefix }}-password" name="password" type="password" class="mt-1 block w-full" autocomplete="new-password" />
                    <p class="mt-1 text-sm text-gray-500">{{ __('Leave blank to keep current password. Setting a new password requires the employee to change it on next login.') }}</p>
                @else
                    <p class="mt-1 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700">
                        {{ __('Temporary password: :password. The employee must change it on first login.', [
                            'password' => config('auth.default_employee_password'),
                        ]) }}
                    </p>
                @endif
                <x-input-error class="mt-2" :messages="$errors->get('password')" />
            </div>

            <div>
                <x-input-label for="{{ $prefix }}-employee_number" :value="__('Employee number')" />
                <x-text-input id="{{ $prefix }}-employee_number" name="employee_number" type="text" class="mt-1 block w-full" :value="$field('employee_number', $employee?->employee_number)" required />
                <x-input-error class="mt-2" :messages="$errors->get('employee_number')" />
            </div>

            <div>
                <x-input-label for="{{ $prefix }}-department_id" :value="__('Department')" />
                <x-ui.select id="{{ $prefix }}-department_id" name="department_id">
                    <option value="">{{ __('Select department') }}</option>
                    @foreach ($departments ?? [] as $department)
                        <option value="{{ $department->id }}" @selected((string) $selectedDepartment === (string) $department->id)>{{ $department->name }}</option>
                    @endforeach
                </x-ui.select>
                <x-input-error class="mt-2" :messages="$errors->get('department_id')" />
            </div>

            <div>
                <x-input-label for="{{ $prefix }}-position" :value="__('Position')" />
                <x-text-input id="{{ $prefix }}-position" name="position" type="text" class="mt-1 block w-full" :value="$field('position', $employee?->position)" />
                <x-input-error class="mt-2" :messages="$errors->get('position')" />
            </div>

            <div>
                <x-input-label for="{{ $prefix }}-phone" :value="__('Phone')" />
                <x-text-input id="{{ $prefix }}-phone" name="phone" type="text" class="mt-1 block w-full" :value="$field('phone', $employee?->phone)" />
                <x-input-error class="mt-2" :messages="$errors->get('phone')" />
            </div>

            <div>
                <x-input-label for="{{ $prefix }}-hire_date" :value="__('Hire date')" />
                <x-text-input id="{{ $prefix }}-hire_date" name="hire_date" type="date" class="mt-1 block w-full" :value="$hireDate" />
                <x-input-error class="mt-2" :messages="$errors->get('hire_date')" />
            </div>

            <div>
                <x-input-label for="{{ $prefix }}-termination_date" :value="__('Termination date')" />
                <x-text-input id="{{ $prefix }}-termination_date" name="termination_date" type="date" class="mt-1 block w-full" :value="$terminationDate" />
                <x-input-error class="mt-2" :messages="$errors->get('termination_date')" />
            </div>

            <div>
                <x-input-label for="{{ $prefix }}-base_salary" :value="__('Base salary')" />
                <x-text-input id="{{ $prefix }}-base_salary" name="base_salary" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="$field('base_salary', $employee?->base_salary)" />
                <x-input-error class="mt-2" :messages="$errors->get('base_salary')" />
            </div>

            <div>
                <x-input-label for="{{ $prefix }}-role" :value="__('Role')" />
                <x-ui.select id="{{ $prefix }}-role" name="role" required>
                    <option value="employee" @selected($currentRole === 'employee')>{{ __('Employee') }}</option>
                    <option value="manager" @selected($currentRole === 'manager')>{{ __('Manager') }}</option>
                    <option value="hr" @selected($currentRole === 'hr')>{{ __('HR') }}</option>
                    @if ($employee)
                        <option value="admin" @selected($currentRole === 'admin')>{{ __('Admin') }}</option>
                    @endif
                </x-ui.select>
                <x-input-error class="mt-2" :messages="$errors->get('role')" />
            </div>

            <div>
                <x-input-label for="{{ $prefix }}-work_schedule_id" :value="__('Work schedule')" />
                <x-ui.select id="{{ $prefix }}-work_schedule_id" name="work_schedule_id">
                    <option value="">{{ __('Select schedule') }}</option>
                    @foreach ($schedules ?? [] as $schedule)
                        <option value="{{ $schedule->id }}" @selected((string) $selectedSchedule === (string) $schedule->id)>{{ $schedule->name }}</option>
                    @endforeach
                </x-ui.select>
                <x-input-error class="mt-2" :messages="$errors->get('work_schedule_id')" />
            </div>

            @if ($employee)
                <div>
                    <x-input-label for="{{ $prefix }}-status" :value="__('Status')" />
                    <x-ui.select id="{{ $prefix }}-status" name="status" required>
                        @foreach ($statuses ?? [] as $status)
                            @php
                                $statusValue = $status instanceof \BackedEnum ? $status->value : (string) $status;
                                $statusLabel = $status instanceof \App\Enums\EmploymentStatus ? $status->label() : $statusValue;
                                $currentStatus = $field(
                                    'status',
                                    $employee->status instanceof \BackedEnum ? $employee->status->value : $employee->status
                                );
                            @endphp
                            <option value="{{ $statusValue }}" @selected((string) $currentStatus === (string) $statusValue)>{{ $statusLabel }}</option>
                        @endforeach
                    </x-ui.select>
                    <x-input-error class="mt-2" :messages="$errors->get('status')" />
                </div>
            @endif
        </div>
    </div>

    <div class="border-t border-gray-100 pt-5">
        <h3 class="text-sm font-semibold text-gray-900">{{ __('Personal information') }}</h3>
        <div class="mt-3 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            <div class="sm:col-span-2 lg:col-span-3">
                <x-input-label for="{{ $prefix }}-photo" :value="__('Photo')" />
                @if ($employee?->photoUrl())
                    <div class="mt-2 mb-3 flex flex-col gap-3 sm:flex-row sm:items-center">
                        <img src="{{ $employee->photoUrl() }}" alt="{{ $employee->user?->name }}" class="h-16 w-16 rounded-full object-cover ring-1 ring-gray-200">
                        <label class="inline-flex items-center gap-2 text-sm text-gray-600">
                            <input type="checkbox" name="remove_photo" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                            {{ __('Remove current photo') }}
                        </label>
                    </div>
                @endif
                <input id="{{ $prefix }}-photo" name="photo" type="file" accept="image/jpeg,image/png,image/webp" class="mt-1 block w-full text-sm text-gray-600 file:mr-3 file:rounded-md file:border-0 file:bg-gray-100 file:px-3 file:py-2 file:text-sm file:font-medium file:text-gray-700 hover:file:bg-gray-200">
                <p class="mt-1 text-xs text-gray-500">{{ __('JPG, PNG, or WebP up to 2MB.') }}</p>
                <x-input-error class="mt-2" :messages="$errors->get('photo')" />
            </div>

            <div>
                <x-input-label for="{{ $prefix }}-date_of_birth" :value="__('Date of birth')" />
                <x-text-input id="{{ $prefix }}-date_of_birth" name="date_of_birth" type="date" class="mt-1 block w-full" :value="$dateOfBirth" />
                <x-input-error class="mt-2" :messages="$errors->get('date_of_birth')" />
            </div>

            <div>
                <x-input-label for="{{ $prefix }}-gender" :value="__('Gender')" />
                <x-ui.select id="{{ $prefix }}-gender" name="gender">
                    <option value="">{{ __('Select gender') }}</option>
                    <option value="male">{{ __('Male') }}</option>
                    <option value="female">{{ __('Female') }}</option>
                </x-ui.select>
                <x-input-error class="mt-2" :messages="$errors->get('gender')" />
            </div>

            <div>
                <x-input-label for="{{ $prefix }}-marital_status" :value="__('Marital status')" />
                <x-ui.select id="{{ $prefix }}-marital_status" name="marital_status">
                    <option value="">{{ __('Select status') }}</option>
                    @foreach ($maritalStatuses ?? [] as $maritalStatus)
                        <option value="{{ $maritalStatus->value }}" @selected((string) $currentMarital === $maritalStatus->value)>{{ $maritalStatus->label() }}</option>
                    @endforeach
                </x-ui.select>
                <x-input-error class="mt-2" :messages="$errors->get('marital_status')" />
            </div>

            <div>
                <x-input-label for="{{ $prefix }}-nationality" :value="__('Nationality')" />
                <x-text-input id="{{ $prefix }}-nationality" name="nationality" type="text" class="mt-1 block w-full" :value="$field('nationality', $employee?->nationality)" />
                <x-input-error class="mt-2" :messages="$errors->get('nationality')" />
            </div>

            <div>
                <x-input-label for="{{ $prefix }}-national_id" :value="__('National ID')" />
                <x-text-input id="{{ $prefix }}-national_id" name="national_id" type="text" class="mt-1 block w-full" :value="$field('national_id', $employee?->national_id)" />
                <x-input-error class="mt-2" :messages="$errors->get('national_id')" />
            </div>

            <div>
                <x-input-label for="{{ $prefix }}-tax_id" :value="__('Tax ID')" />
                <x-text-input id="{{ $prefix }}-tax_id" name="tax_id" type="text" class="mt-1 block w-full" :value="$field('tax_id', $employee?->tax_id)" />
                <x-input-error class="mt-2" :messages="$errors->get('tax_id')" />
            </div>

            <div>
                <x-input-label for="{{ $prefix }}-personal_email" :value="__('Personal email')" />
                <x-text-input id="{{ $prefix }}-personal_email" name="personal_email" type="email" class="mt-1 block w-full" :value="$field('personal_email', $employee?->personal_email)" />
                <x-input-error class="mt-2" :messages="$errors->get('personal_email')" />
            </div>

            <div>
                <x-input-label for="{{ $prefix }}-blood_group" :value="__('Blood group')" />
                <x-text-input id="{{ $prefix }}-blood_group" name="blood_group" type="text" class="mt-1 block w-full" :value="$field('blood_group', $employee?->blood_group)" placeholder="O+" />
                <x-input-error class="mt-2" :messages="$errors->get('blood_group')" />
            </div>

            <div class="sm:col-span-2 lg:col-span-2">
                <x-input-label for="{{ $prefix }}-address" :value="__('Address')" />
                <x-text-input id="{{ $prefix }}-address" name="address" type="text" class="mt-1 block w-full" :value="$field('address', $employee?->address)" />
                <x-input-error class="mt-2" :messages="$errors->get('address')" />
            </div>

            <div>
                <x-input-label for="{{ $prefix }}-bank_account" :value="__('Bank account')" />
                <x-text-input id="{{ $prefix }}-bank_account" name="bank_account" type="text" class="mt-1 block w-full" :value="$field('bank_account', $employee?->bank_account)" />
                <x-input-error class="mt-2" :messages="$errors->get('bank_account')" />
            </div>
        </div>
    </div>

    @if (($payComponents ?? collect())->isNotEmpty())
        <div class="border-t border-gray-100 pt-5">
            <h3 class="text-sm font-semibold text-gray-900">{{ __('Pay components') }}</h3>
            <p class="mt-1 text-sm text-gray-500">{{ __('Enable allowances or deductions for this employee. Percent components use % of base salary.') }}</p>
            <div class="mt-3 grid gap-4 sm:grid-cols-2">
                @foreach ($payComponents as $payComponent)
                    @php
                        $assigned = $assignedComponents->get($payComponent->id);
                        $legacyAmount = match ($payComponent->code) {
                            'housing_allowance' => $employee?->housing_allowance,
                            'transport_allowance' => $employee?->transport_allowance,
                            default => null,
                        };
                        $defaultAmount = old(
                            'pay_components.'.$payComponent->id.'.amount',
                            $assigned?->pivot?->amount ?? $legacyAmount ?? $payComponent->default_amount
                        );
                        $enabled = (bool) old(
                            'pay_components.'.$payComponent->id.'.enabled',
                            $assigned !== null || (float) ($legacyAmount ?? 0) > 0
                        );
                        $amountLabel = $payComponent->calculation->value === 'percent_of_base' ? __('Percent') : __('Amount');
                    @endphp
                    <div class="rounded-lg border border-gray-200 p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $payComponent->name }}</p>
                                <p class="text-xs text-gray-500">
                                    {{ $payComponent->type->label() }}
                                    · {{ $payComponent->calculation->label() }}
                                    @if ($payComponent->is_taxable && $payComponent->type->value === 'earning')
                                        · {{ __('Taxable') }}
                                    @endif
                                </p>
                            </div>
                            <label class="inline-flex items-center gap-2 text-xs text-gray-600">
                                <input
                                    type="checkbox"
                                    name="pay_components[{{ $payComponent->id }}][enabled]"
                                    value="1"
                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                    @checked($enabled)
                                />
                                {{ __('Enable') }}
                            </label>
                        </div>
                        <div class="mt-3">
                            <x-input-label for="{{ $prefix }}-pay-component-{{ $payComponent->id }}" :value="$amountLabel" />
                            <x-text-input
                                id="{{ $prefix }}-pay-component-{{ $payComponent->id }}"
                                name="pay_components[{{ $payComponent->id }}][amount]"
                                type="number"
                                step="0.01"
                                min="0"
                                class="mt-1 block w-full"
                                :value="$defaultAmount"
                            />
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="border-t border-gray-100 pt-5">
        <h3 class="text-sm font-semibold text-gray-900">{{ __('Emergency contact') }}</h3>
        <div class="mt-3 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            <div>
                <x-input-label for="{{ $prefix }}-emergency_contact_name" :value="__('Contact name')" />
                <x-text-input id="{{ $prefix }}-emergency_contact_name" name="emergency_contact_name" type="text" class="mt-1 block w-full" :value="$field('emergency_contact_name', $employee?->emergency_contact_name)" />
                <x-input-error class="mt-2" :messages="$errors->get('emergency_contact_name')" />
            </div>
            <div>
                <x-input-label for="{{ $prefix }}-emergency_contact_phone" :value="__('Contact phone')" />
                <x-text-input id="{{ $prefix }}-emergency_contact_phone" name="emergency_contact_phone" type="text" class="mt-1 block w-full" :value="$field('emergency_contact_phone', $employee?->emergency_contact_phone)" />
                <x-input-error class="mt-2" :messages="$errors->get('emergency_contact_phone')" />
            </div>
            <div>
                <x-input-label for="{{ $prefix }}-emergency_contact_relationship" :value="__('Relationship')" />
                <x-text-input id="{{ $prefix }}-emergency_contact_relationship" name="emergency_contact_relationship" type="text" class="mt-1 block w-full" :value="$field('emergency_contact_relationship', $employee?->emergency_contact_relationship)" />
                <x-input-error class="mt-2" :messages="$errors->get('emergency_contact_relationship')" />
            </div>
            <div class="sm:col-span-2 lg:col-span-3">
                <x-input-label for="{{ $prefix }}-notes" :value="__('Notes')" />
                <textarea id="{{ $prefix }}-notes" name="notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ $field('notes', $employee?->notes) }}</textarea>
                <x-input-error class="mt-2" :messages="$errors->get('notes')" />
            </div>
        </div>
    </div>

    <div class="flex flex-col-reverse gap-3 border-t border-gray-100 pt-4 sm:flex-row sm:items-center sm:justify-end">
        <a href="{{ $cancelUrl }}">
            <x-secondary-button type="button" class="w-full sm:w-auto">{{ __('Cancel') }}</x-secondary-button>
        </a>
        <x-primary-button class="w-full sm:w-auto">{{ $employee ? __('Save changes') : __('Create employee') }}</x-primary-button>
    </div>
</form>
