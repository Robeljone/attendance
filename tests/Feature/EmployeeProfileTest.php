<?php

namespace Tests\Feature;

use App\Enums\EducationLevel;
use App\Enums\EmployeeDocumentType;
use App\Enums\EmploymentStatus;
use App\Enums\Gender;
use App\Enums\MaritalStatus;
use App\Enums\UserRole;
use App\Models\Employee;
use App\Models\EmployeeDocument;
use App\Models\EmployeeEducation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EmployeeProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_employee_personal_profile_and_photo(): void
    {
        Storage::fake('public');

        $admin = User::factory()->admin()->create();
        $user = User::factory()->create(['role' => UserRole::Employee, 'name' => 'Alex Employee']);
        $employee = Employee::factory()->create([
            'user_id' => $user->id,
            'employee_number' => 'EMP-200',
            'status' => EmploymentStatus::Active,
        ]);

        $photo = UploadedFile::fake()->image('portrait.jpg', 300, 300);

        $response = $this->actingAs($admin)->put(route('admin.employees.update', $employee), [
            'name' => 'Alex Updated',
            'email' => $user->email,
            'role' => UserRole::Employee->value,
            'employee_number' => 'EMP-200',
            'status' => EmploymentStatus::Active->value,
            'date_of_birth' => '1995-04-12',
            'gender' => Gender::Male->value,
            'marital_status' => MaritalStatus::Single->value,
            'nationality' => 'Ethiopian',
            'national_id' => 'ID-998877',
            'tax_id' => 'TIN-7788',
            'personal_email' => 'alex.home@example.test',
            'blood_group' => 'O+',
            'address' => '12 Demo Street',
            'emergency_contact_name' => 'Jordan Contact',
            'emergency_contact_phone' => '+15551212',
            'emergency_contact_relationship' => 'Sibling',
            'photo' => $photo,
        ]);

        $response->assertRedirect(route('admin.employees.show', $employee));

        $employee->refresh();

        $this->assertSame('Alex Updated', $employee->user->name);
        $this->assertSame(Gender::Male, $employee->gender);
        $this->assertSame('Ethiopian', $employee->nationality);
        $this->assertSame('ID-998877', $employee->national_id);
        $this->assertSame('O+', $employee->blood_group);
        $this->assertNotNull($employee->photo_path);
        Storage::disk('public')->assertExists($employee->photo_path);
    }

    public function test_admin_can_manage_employee_education_records(): void
    {
        $admin = User::factory()->admin()->create();
        $employee = Employee::factory()->create();

        $this->actingAs($admin)
            ->post(route('admin.employees.educations.store', $employee), [
                'institution' => 'Addis Ababa University',
                'level' => EducationLevel::Bachelor->value,
                'field_of_study' => 'Computer Science',
                'degree_title' => 'BSc Computer Science',
                'start_year' => 2014,
                'end_year' => 2018,
                'grade' => '3.5 GPA',
                'is_highest' => '1',
                'form_modal' => 'add-education',
            ])
            ->assertRedirect(route('admin.employees.show', $employee));

        $education = EmployeeEducation::query()->where('employee_id', $employee->id)->first();
        $this->assertNotNull($education);
        $this->assertTrue($education->is_highest);
        $this->assertSame(EducationLevel::Bachelor, $education->level);

        $this->actingAs($admin)
            ->put(route('admin.employees.educations.update', [$employee, $education]), [
                'institution' => 'Addis Ababa University',
                'level' => EducationLevel::Master->value,
                'field_of_study' => 'Software Engineering',
                'degree_title' => 'MSc Software Engineering',
                'start_year' => 2019,
                'end_year' => 2021,
                'grade' => 'Distinction',
                'is_highest' => '1',
                'form_modal' => 'edit-education-'.$education->id,
            ])
            ->assertRedirect(route('admin.employees.show', $employee));

        $this->assertSame(EducationLevel::Master, $education->fresh()->level);

        $this->actingAs($admin)
            ->delete(route('admin.employees.educations.destroy', [$employee, $education]))
            ->assertRedirect(route('admin.employees.show', $employee));

        $this->assertDatabaseMissing('employee_educations', ['id' => $education->id]);
    }

    public function test_admin_can_upload_download_and_remove_employee_documents(): void
    {
        Storage::fake('local');

        $admin = User::factory()->admin()->create();
        $employee = Employee::factory()->create();
        $file = UploadedFile::fake()->create('national-id.pdf', 120, 'application/pdf');

        $this->actingAs($admin)
            ->post(route('admin.employees.documents.store', $employee), [
                'type' => EmployeeDocumentType::NationalId->value,
                'title' => 'National ID Scan',
                'document' => $file,
                'document_number' => 'ID-123456',
                'issued_on' => '2022-01-15',
                'expires_on' => '2032-01-15',
                'form_modal' => 'add-document',
            ])
            ->assertRedirect(route('admin.employees.show', $employee));

        $document = EmployeeDocument::query()->where('employee_id', $employee->id)->first();
        $this->assertNotNull($document);
        $this->assertSame('National ID Scan', $document->title);
        Storage::disk('local')->assertExists($document->file_path);

        $this->actingAs($admin)
            ->get(route('admin.employees.documents.download', [$employee, $document]))
            ->assertOk();

        $this->actingAs($admin)
            ->delete(route('admin.employees.documents.destroy', [$employee, $document]))
            ->assertRedirect(route('admin.employees.show', $employee));

        $this->assertDatabaseMissing('employee_documents', ['id' => $document->id]);
        Storage::disk('local')->assertMissing($document->file_path);
    }

    public function test_employee_show_page_includes_education_and_documents_sections(): void
    {
        $admin = User::factory()->admin()->create();
        $employee = Employee::factory()->create();

        EmployeeEducation::factory()->highest()->create([
            'employee_id' => $employee->id,
            'institution' => 'Unity University',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.employees.show', $employee))
            ->assertOk()
            ->assertSee('Personal information')
            ->assertSee('Education')
            ->assertSee('Documents')
            ->assertSee('Unity University')
            ->assertSee('Add education')
            ->assertSee('Upload document');
    }

    public function test_portal_profile_shows_education_records(): void
    {
        $user = User::factory()->create(['role' => UserRole::Employee]);
        $employee = Employee::factory()->create(['user_id' => $user->id]);

        EmployeeEducation::factory()->highest()->create([
            'employee_id' => $employee->id,
            'institution' => 'Portal College',
            'field_of_study' => 'Business',
        ]);

        $this->actingAs($user)
            ->get(route('portal.profile.show'))
            ->assertOk()
            ->assertSee('Portal College')
            ->assertSee('Business')
            ->assertSee('Education');
    }
}
