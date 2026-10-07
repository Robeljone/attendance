<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EducationLevel;
use App\Enums\EmployeeDocumentType;
use App\Enums\EmploymentStatus;
use App\Enums\Gender;
use App\Enums\MaritalStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreEmployeeRequest;
use App\Http\Requests\Admin\UpdateEmployeeRequest;
use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use App\Models\WorkSchedule;
use App\Support\ResolvesIndexPagination;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    public function index(Request $request): View
    {
        $search = ResolvesIndexPagination::search($request);

        $employees = Employee::query()
            ->with(['user', 'department', 'workSchedules'])
            ->when($search, function ($query) use ($search): void {
                $term = '%'.$search.'%';
                $query->where(function ($inner) use ($term): void {
                    $inner->where('employee_number', 'like', $term)
                        ->orWhere('position', 'like', $term)
                        ->orWhereHas('user', fn ($user) => $user->where('name', 'like', $term)->orWhere('email', 'like', $term))
                        ->orWhereHas('department', fn ($department) => $department->where('name', 'like', $term));
                });
            })
            ->latest()
            ->paginate(ResolvesIndexPagination::perPage($request))
            ->withQueryString();

        return view('admin.employees.index', [
            'employees' => $employees,
        ]);
    }

    public function create(): View
    {
        return view('admin.employees.create', $this->formOptions());
    }

    public function store(StoreEmployeeRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $employee = DB::transaction(function () use ($request, $validated): Employee {
            $user = User::query()->create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make((string) config('auth.default_employee_password')),
                'role' => $validated['role'],
                'is_active' => true,
                'must_change_password' => true,
                'email_verified_at' => now(),
            ]);

            $employee = Employee::query()->create($this->employeeAttributes($validated, [
                'user_id' => $user->id,
                'status' => $validated['status'] ?? EmploymentStatus::Active,
                'base_salary' => $validated['base_salary'] ?? 0,
            ]));

            if ($request->hasFile('photo')) {
                $employee->update([
                    'photo_path' => $request->file('photo')->store('employee-photos', 'public'),
                ]);
            }

            if (! empty($validated['work_schedule_id'])) {
                $employee->workSchedules()->attach($validated['work_schedule_id'], [
                    'effective_from' => now()->toDateString(),
                ]);
            }

            return $employee;
        });

        return redirect()
            ->route('admin.employees.show', $employee)
            ->with('success', __('Employee created successfully. Temporary password: :password. They must change it on first login.', [
                'password' => config('auth.default_employee_password'),
            ]));
    }

    public function show(Employee $employee): View
    {
        $employee->load(['user', 'department', 'workSchedules', 'educations', 'documents']);

        $attendance = $employee->attendanceRecords()
            ->latest('work_date')
            ->limit(14)
            ->get();

        return view('admin.employees.show', [
            'employee' => $employee,
            'attendance' => $attendance,
            'educationLevels' => EducationLevel::cases(),
            'documentTypes' => EmployeeDocumentType::cases(),
        ]);
    }

    public function edit(Employee $employee): View
    {
        $employee->load(['user', 'department', 'workSchedules']);

        return view('admin.employees.edit', [
            'employee' => $employee,
            ...$this->formOptions(),
        ]);
    }

    public function update(UpdateEmployeeRequest $request, Employee $employee): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($request, $validated, $employee): void {
            $userData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'role' => $validated['role'],
                'is_active' => $validated['is_active'] ?? true,
            ];

            if (! empty($validated['password'])) {
                $userData['password'] = Hash::make($validated['password']);
                $userData['must_change_password'] = true;
            }

            $employee->user->update($userData);

            $attributes = $this->employeeAttributes($validated, [
                'status' => $validated['status'],
                'base_salary' => $validated['base_salary'] ?? 0,
            ]);

            if ($request->boolean('remove_photo') && $employee->photo_path) {
                $employee->deletePhoto();
                $attributes['photo_path'] = null;
            }

            if ($request->hasFile('photo')) {
                $employee->deletePhoto();
                $attributes['photo_path'] = $request->file('photo')->store('employee-photos', 'public');
            }

            $employee->update($attributes);

            if (! empty($validated['work_schedule_id'])) {
                $employee->workSchedules()->sync([
                    $validated['work_schedule_id'] => ['effective_from' => now()->toDateString()],
                ]);
            }
        });

        return redirect()->route('admin.employees.show', $employee)->with('success', 'Employee updated successfully.');
    }

    public function destroy(Employee $employee): RedirectResponse
    {
        $employee->load('documents');

        $employee->documents->each(function ($document): void {
            $document->deleteFile();
        });
        $employee->deletePhoto();
        $employee->user()->delete();

        return redirect()->route('admin.employees.index')->with('success', 'Employee deleted.');
    }

    /**
     * @return array{
     *     departments: Collection<int, Department>,
     *     schedules: Collection<int, WorkSchedule>,
     *     statuses: list<EmploymentStatus>,
     *     genders: list<Gender>,
     *     maritalStatuses: list<MaritalStatus>
     * }
     */
    private function formOptions(): array
    {
        return [
            'departments' => Department::query()->where('is_active', true)->orderBy('name')->get(),
            'schedules' => WorkSchedule::query()->where('is_active', true)->orderBy('name')->get(),
            'statuses' => EmploymentStatus::cases(),
            'genders' => Gender::cases(),
            'maritalStatuses' => MaritalStatus::cases(),
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function employeeAttributes(array $validated, array $overrides = []): array
    {
        return array_merge([
            'department_id' => $validated['department_id'] ?? null,
            'employee_number' => $validated['employee_number'],
            'phone' => $validated['phone'] ?? null,
            'position' => $validated['position'] ?? null,
            'hire_date' => $validated['hire_date'] ?? null,
            'date_of_birth' => $validated['date_of_birth'] ?? null,
            'address' => $validated['address'] ?? null,
            'bank_account' => $validated['bank_account'] ?? null,
            'gender' => $validated['gender'] ?? null,
            'marital_status' => $validated['marital_status'] ?? null,
            'nationality' => $validated['nationality'] ?? null,
            'national_id' => $validated['national_id'] ?? null,
            'tax_id' => $validated['tax_id'] ?? null,
            'personal_email' => $validated['personal_email'] ?? null,
            'blood_group' => $validated['blood_group'] ?? null,
            'emergency_contact_name' => $validated['emergency_contact_name'] ?? null,
            'emergency_contact_phone' => $validated['emergency_contact_phone'] ?? null,
            'emergency_contact_relationship' => $validated['emergency_contact_relationship'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ], $overrides);
    }
}
