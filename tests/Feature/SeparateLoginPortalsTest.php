<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeparateLoginPortalsTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_shows_portal_chooser_for_guests(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee(__('Employee portal'))
            ->assertSee(__('Staff portal'));
    }

    public function test_employee_can_sign_in_via_employee_login(): void
    {
        $user = User::factory()->create();

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticatedAs($user);
    }

    public function test_staff_can_sign_in_via_admin_login(): void
    {
        $user = User::factory()->admin()->create();

        $this->post(route('admin.login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticatedAs($user);
    }

    public function test_manager_can_sign_in_via_admin_login(): void
    {
        $user = User::factory()->manager()->create();

        $this->post(route('admin.login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticatedAs($user);
    }

    public function test_staff_cannot_sign_in_via_employee_login(): void
    {
        $user = User::factory()->hr()->create();

        $this->from(route('login'))
            ->post(route('login.store'), [
                'email' => $user->email,
                'password' => 'password',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_employee_cannot_sign_in_via_admin_login(): void
    {
        $user = User::factory()->create();

        $this->from(route('admin.login'))
            ->post(route('admin.login.store'), [
                'email' => $user->email,
                'password' => 'password',
            ])
            ->assertRedirect(route('admin.login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_guest_accessing_admin_area_is_sent_to_staff_login(): void
    {
        $this->get(route('admin.employees.index'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_guest_accessing_portal_is_sent_to_employee_login(): void
    {
        $this->get(route('portal.attendance.index'))
            ->assertRedirect(route('login'));
    }

    public function test_staff_logout_returns_to_staff_login(): void
    {
        $user = User::factory()->admin()->create();

        $this->actingAs($user)
            ->post(route('logout'))
            ->assertRedirect(route('admin.login'));

        $this->assertGuest();
    }

    public function test_employee_logout_returns_to_employee_login(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }
}
