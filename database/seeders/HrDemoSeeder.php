<?php

namespace Database\Seeders;

use App\Enums\EducationLevel;
use App\Enums\EmployeeDocumentType;
use App\Enums\EmploymentStatus;
use App\Enums\Gender;
use App\Enums\MaritalStatus;
use App\Enums\UserRole;
use App\Models\CompanySetting;
use App\Models\Department;
use App\Models\Employee;
use App\Models\LeaveType;
use App\Models\PayComponent;
use App\Models\User;
use App\Models\WorkSchedule;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class HrDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(PayComponentSeeder::class);

        CompanySetting::query()->updateOrCreate(['id' => 1], [
            'company_name' => 'Attendance HR',
            'timezone' => 'UTC',
            'allowed_ip_cidrs' => ['127.0.0.1/32', '::1/128', '192.168.0.0/16', '10.0.0.0/8'],
            'enforce_company_network' => true,
            'currency' => 'USD',
            'income_tax_percent' => 10,
            'pension_percent' => 7,
        ]);

        $hr = Department::query()->updateOrCreate(['code' => 'HR'], [
            'name' => 'Human Resources',
            'description' => 'People operations',
            'is_active' => true,
        ]);

        $eng = Department::query()->updateOrCreate(['code' => 'ENG'], [
            'name' => 'Engineering',
            'description' => 'Product engineering',
            'is_active' => true,
        ]);

        $finance = Department::query()->updateOrCreate(['code' => 'FIN'], [
            'name' => 'Finance',
            'description' => 'Payroll and accounting',
            'is_active' => true,
        ]);

        $standard = WorkSchedule::query()->updateOrCreate(['name' => 'Standard 9-5'], [
            'start_time' => '09:00',
            'end_time' => '17:00',
            'work_days' => [1, 2, 3, 4, 5],
            'break_minutes' => 60,
            'is_active' => true,
        ]);

        LeaveType::query()->upsert([
            ['name' => 'Annual Leave', 'code' => 'ANNUAL', 'default_days_per_year' => 20, 'is_paid' => true, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Sick Leave', 'code' => 'SICK', 'default_days_per_year' => 10, 'is_paid' => true, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Unpaid Leave', 'code' => 'UNPAID', 'default_days_per_year' => 0, 'is_paid' => false, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ], ['code'], ['name', 'default_days_per_year', 'is_paid', 'is_active', 'updated_at']);

        User::query()->updateOrCreate(['email' => 'superadmin@company.test'], [
            'name' => 'Super Admin',
            'password' => Hash::make('password'),
            'role' => UserRole::SuperAdmin,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $adminUser = User::query()->updateOrCreate(['email' => 'admin@company.test'], [
            'name' => 'System Admin',
            'password' => Hash::make('password'),
            'role' => UserRole::Admin,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $adminEmployee = Employee::query()->updateOrCreate(['employee_number' => 'EMP-001'], [
            'user_id' => $adminUser->id,
            'department_id' => $hr->id,
            'phone' => '+10000000001',
            'position' => 'HR Director',
            'hire_date' => now()->subYears(3)->toDateString(),
            'date_of_birth' => now()->subYears(38)->toDateString(),
            'address' => '120 People Ops Ave',
            'emergency_contact_name' => 'Sam Admin',
            'emergency_contact_phone' => '+10000000011',
            'emergency_contact_relationship' => 'Spouse',
            'base_salary' => 7500,
            'housing_allowance' => 800,
            'transport_allowance' => 300,
            'bank_account' => 'BANK-001-7788',
            'status' => EmploymentStatus::Active,
            'gender' => Gender::Female,
            'marital_status' => MaritalStatus::Married,
            'nationality' => 'Ethiopian',
            'national_id' => 'ID-77881122',
            'tax_id' => 'TIN-100200',
            'personal_email' => 'admin.personal@example.test',
            'blood_group' => 'O+',
        ]);
        $adminEmployee->workSchedules()->syncWithoutDetaching([
            $standard->id => ['effective_from' => now()->subYears(3)->toDateString()],
        ]);
        $this->seedEducationAndDocuments($adminEmployee, [
            'institution' => 'Addis Ababa University',
            'level' => EducationLevel::Master,
            'field_of_study' => 'Human Resource Management',
            'degree_title' => 'MSc Human Resources',
            'start_year' => 2010,
            'end_year' => 2012,
            'grade' => 'Distinction',
        ], 'Employment Contract');

        $employeeUser = User::query()->updateOrCreate(['email' => 'employee@company.test'], [
            'name' => 'Alex Employee',
            'password' => Hash::make('password'),
            'role' => UserRole::Employee,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $employee = Employee::query()->updateOrCreate(['employee_number' => 'EMP-100'], [
            'user_id' => $employeeUser->id,
            'department_id' => $eng->id,
            'phone' => '+10000000002',
            'position' => 'Software Engineer',
            'hire_date' => now()->subYear()->toDateString(),
            'date_of_birth' => now()->subYears(28)->toDateString(),
            'address' => '45 Engineering Lane',
            'emergency_contact_name' => 'Jordan Employee',
            'emergency_contact_phone' => '+10000000022',
            'emergency_contact_relationship' => 'Sibling',
            'base_salary' => 5200,
            'housing_allowance' => 500,
            'transport_allowance' => 250,
            'bank_account' => 'BANK-100-4422',
            'status' => EmploymentStatus::Active,
            'gender' => Gender::Male,
            'marital_status' => MaritalStatus::Single,
            'nationality' => 'Ethiopian',
            'national_id' => 'ID-10020030',
            'tax_id' => 'TIN-300400',
            'personal_email' => 'alex.personal@example.test',
            'blood_group' => 'A+',
        ]);
        $employee->workSchedules()->syncWithoutDetaching([
            $standard->id => ['effective_from' => now()->subYear()->toDateString()],
        ]);
        $this->seedEducationAndDocuments($employee, [
            'institution' => 'Bahir Dar University',
            'level' => EducationLevel::Bachelor,
            'field_of_study' => 'Computer Science',
            'degree_title' => 'BSc Computer Science',
            'start_year' => 2014,
            'end_year' => 2018,
            'grade' => '3.6 GPA',
        ], 'National ID Scan');

        $managerUser = User::query()->updateOrCreate(['email' => 'manager@company.test'], [
            'name' => 'Morgan Manager',
            'password' => Hash::make('password'),
            'role' => UserRole::Manager,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $manager = Employee::query()->updateOrCreate(['employee_number' => 'EMP-050'], [
            'user_id' => $managerUser->id,
            'department_id' => $finance->id,
            'phone' => '+10000000003',
            'position' => 'Finance Manager',
            'hire_date' => now()->subYears(2)->toDateString(),
            'date_of_birth' => now()->subYears(34)->toDateString(),
            'address' => '88 Ledger Street',
            'emergency_contact_name' => 'Casey Manager',
            'emergency_contact_phone' => '+10000000033',
            'emergency_contact_relationship' => 'Spouse',
            'base_salary' => 6400,
            'housing_allowance' => 600,
            'transport_allowance' => 250,
            'bank_account' => 'BANK-050-9911',
            'status' => EmploymentStatus::Active,
            'gender' => Gender::Female,
            'marital_status' => MaritalStatus::Married,
            'nationality' => 'Ethiopian',
            'national_id' => 'ID-05060708',
            'tax_id' => 'TIN-500600',
            'personal_email' => 'morgan.personal@example.test',
            'blood_group' => 'B+',
        ]);
        $manager->workSchedules()->syncWithoutDetaching([
            $standard->id => ['effective_from' => now()->subYears(2)->toDateString()],
        ]);
        $this->seedEducationAndDocuments($manager, [
            'institution' => 'Unity University',
            'level' => EducationLevel::Master,
            'field_of_study' => 'Accounting and Finance',
            'degree_title' => 'MBA Finance',
            'start_year' => 2012,
            'end_year' => 2014,
            'grade' => 'First Class',
        ], 'Tax Certificate');

        foreach ([$adminEmployee, $employee, $manager] as $seededEmployee) {
            $this->syncLegacyAllowancesToComponents($seededEmployee);
        }
    }

    private function syncLegacyAllowancesToComponents(Employee $employee): void
    {
        $housing = PayComponent::query()->where('code', 'housing_allowance')->first();
        $transport = PayComponent::query()->where('code', 'transport_allowance')->first();
        $sync = [];

        if ($housing && (float) $employee->housing_allowance > 0) {
            $sync[$housing->id] = [
                'amount' => $employee->housing_allowance,
                'is_enabled' => true,
            ];
        }

        if ($transport && (float) $employee->transport_allowance > 0) {
            $sync[$transport->id] = [
                'amount' => $employee->transport_allowance,
                'is_enabled' => true,
            ];
        }

        if ($sync !== []) {
            $employee->payComponents()->syncWithoutDetaching($sync);
        }
    }

    /**
     * @param  array<string, mixed>  $education
     */
    private function seedEducationAndDocuments(Employee $employee, array $education, string $documentTitle): void
    {
        $employee->educations()->updateOrCreate(
            [
                'employee_id' => $employee->id,
                'institution' => $education['institution'],
                'level' => $education['level'],
            ],
            [
                ...$education,
                'is_highest' => true,
            ]
        );

        $path = 'employee-documents/'.$employee->id.'/demo-'.str($documentTitle)->slug().'.txt';
        Storage::disk('local')->put($path, "Demo scanned document for {$documentTitle}");

        $employee->documents()->updateOrCreate(
            [
                'employee_id' => $employee->id,
                'title' => $documentTitle,
            ],
            [
                'type' => match (true) {
                    str_contains($documentTitle, 'Contract') => EmployeeDocumentType::Contract,
                    str_contains($documentTitle, 'Tax') => EmployeeDocumentType::Tax,
                    default => EmployeeDocumentType::NationalId,
                },
                'file_path' => $path,
                'original_name' => str($documentTitle)->slug().'.txt',
                'mime_type' => 'text/plain',
                'file_size' => Storage::disk('local')->size($path),
                'issued_on' => now()->subYears(1)->toDateString(),
                'expires_on' => now()->addYears(4)->toDateString(),
            ]
        );
    }
}
