<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class EmployeeCreationPasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_employee_form_shows_temporary_password_without_input(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.employees.create'))
            ->assertOk()
            ->assertSee(__('Password'))
            ->assertSee(__('Temporary password: :password. The employee must change it on first login.', [
                'password' => config('auth.default_employee_password'),
            ]))
            ->assertDontSee('name="password"', false);
    }

    public function test_creating_employee_assigns_default_password_and_requires_change(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post(route('admin.employees.store'), [
            'name' => 'New Hire',
            'email' => 'new.hire@example.test',
            'role' => UserRole::Employee->value,
            'employee_number' => 'EMP-9001',
        ]);

        $user = User::query()->where('email', 'new.hire@example.test')->first();

        $this->assertNotNull($user);
        $response->assertRedirect(route('admin.employees.show', $user->employee));
        $this->assertTrue($user->must_change_password);
        $this->assertTrue(Hash::check((string) config('auth.default_employee_password'), $user->password));
    }
}
