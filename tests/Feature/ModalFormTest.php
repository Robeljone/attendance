<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModalFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_routes_redirect_to_index_modals(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.departments.create'))
            ->assertRedirect(route('admin.departments.index', ['modal' => 'create']));

        $this->actingAs($admin)
            ->get(route('admin.payroll.create'))
            ->assertRedirect(route('admin.payroll.index', ['modal' => 'create']));
    }

    public function test_employee_create_and_edit_use_dedicated_pages(): void
    {
        $admin = User::factory()->admin()->create();
        $employee = Employee::factory()->create();

        $this->actingAs($admin)
            ->get(route('admin.employees.create'))
            ->assertOk()
            ->assertSee('Add employee')
            ->assertSee('name="employee_number"', false)
            ->assertSee('Personal information')
            ->assertDontSee('open-modal', false);

        $this->actingAs($admin)
            ->get(route('admin.employees.edit', $employee))
            ->assertOk()
            ->assertSee('Edit employee')
            ->assertSee('name="employee_number"', false)
            ->assertSee($employee->employee_number)
            ->assertDontSee('open-modal', false);
    }

    public function test_department_can_be_created_from_index_modal(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->from(route('admin.departments.index'))->post(route('admin.departments.store'), [
            'form_modal' => 'create-department',
            'name' => 'People Ops',
            'code' => 'POPS',
            'description' => 'HR department',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('admin.departments.index'));
        $this->assertDatabaseHas('departments', [
            'name' => 'People Ops',
            'code' => 'POPS',
        ]);
    }

    public function test_department_validation_errors_reopen_create_modal(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)
            ->from(route('admin.departments.index'))
            ->post(route('admin.departments.store'), [
                'form_modal' => 'create-department',
                'name' => '',
                'code' => '',
            ]);

        $response->assertRedirect(route('admin.departments.index'));
        $response->assertSessionHasErrors(['name', 'code']);

        $this->actingAs($admin)
            ->get(route('admin.departments.index'))
            ->assertOk()
            ->assertSee('name="form_modal"', false)
            ->assertSee('create-department', false);
    }

    public function test_department_edit_route_opens_index_modal(): void
    {
        $admin = User::factory()->admin()->create();
        $department = Department::query()->create([
            'name' => 'Engineering',
            'code' => 'ENG',
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.departments.edit', $department))
            ->assertRedirect(route('admin.departments.index', [
                'modal' => 'edit',
                'id' => $department->id,
            ]));
    }

    public function test_employee_index_links_to_create_page_without_modals(): void
    {
        $admin = User::factory()->admin()->create([
            'role' => UserRole::Admin,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.employees.index'))
            ->assertOk()
            ->assertSee(route('admin.employees.create'), false)
            ->assertDontSee('open-modal', false)
            ->assertDontSee('name="employee_number"', false);
    }
}
