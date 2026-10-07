<?php

namespace Tests\Feature;

use App\Enums\EmploymentStatus;
use App\Enums\UserRole;
use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDatatableTest extends TestCase
{
    use RefreshDatabase;

    public function test_employees_index_supports_search_and_per_page(): void
    {
        $admin = User::factory()->admin()->create();

        $alice = User::factory()->create(['name' => 'Alice Anderson', 'role' => UserRole::Employee]);
        $bob = User::factory()->create(['name' => 'Bob Baker', 'role' => UserRole::Employee]);

        Employee::query()->create([
            'user_id' => $alice->id,
            'employee_number' => 'EMP-A1',
            'position' => 'Designer',
            'base_salary' => 1000,
            'status' => EmploymentStatus::Active,
        ]);

        Employee::query()->create([
            'user_id' => $bob->id,
            'employee_number' => 'EMP-B2',
            'position' => 'Engineer',
            'base_salary' => 1200,
            'status' => EmploymentStatus::Active,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.employees.index', [
            'q' => 'Alice',
            'per_page' => 10,
        ]));

        $response->assertOk();
        $response->assertSee('Alice Anderson');
        $response->assertDontSee('Bob Baker');
        $response->assertSee('name="q"', false);
        $response->assertSee('name="per_page"', false);
        $response->assertSee('View');
        $response->assertSee('Edit');
    }

    public function test_departments_index_filters_by_search_term(): void
    {
        $admin = User::factory()->admin()->create();

        Department::query()->create([
            'name' => 'Engineering',
            'code' => 'ENG',
            'is_active' => true,
        ]);

        Department::query()->create([
            'name' => 'Finance',
            'code' => 'FIN',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.departments.index', [
            'q' => 'FIN',
        ]));

        $response->assertOk();
        $response->assertSee('Finance');
        $response->assertDontSee('Engineering');
        $response->assertSee('Delete');
    }

    public function test_invalid_per_page_falls_back_to_default(): void
    {
        $admin = User::factory()->admin()->create();

        for ($i = 1; $i <= 20; $i++) {
            $user = User::factory()->create(['role' => UserRole::Employee]);
            Employee::query()->create([
                'user_id' => $user->id,
                'employee_number' => 'EMP-'.$i,
                'base_salary' => 1000,
                'status' => EmploymentStatus::Active,
            ]);
        }

        $response = $this->actingAs($admin)->get(route('admin.employees.index', [
            'per_page' => 999,
        ]));

        $response->assertOk();
        $this->assertCount(15, $response->viewData('employees'));
    }
}
