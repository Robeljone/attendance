<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ForcePasswordChangeTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_redirects_to_forced_password_change_when_required(): void
    {
        $user = User::factory()->create([
            'must_change_password' => true,
        ]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('password.change-required'));
    }

    public function test_user_with_must_change_password_cannot_access_dashboard(): void
    {
        $user = User::factory()->create([
            'must_change_password' => true,
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertRedirect(route('password.change-required'));
    }

    public function test_updating_password_clears_must_change_password_flag(): void
    {
        $user = User::factory()->create([
            'must_change_password' => true,
        ]);

        $this->actingAs($user)
            ->from(route('password.change-required'))
            ->put('/password', [
                'current_password' => 'password',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ])
            ->assertRedirect(route('dashboard'));

        $user->refresh();

        $this->assertFalse($user->must_change_password);
        $this->assertTrue(Hash::check('new-password', $user->password));
    }
}
