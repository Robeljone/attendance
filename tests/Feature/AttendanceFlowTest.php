<?php

namespace Tests\Feature;

use App\Enums\EmploymentStatus;
use App\Enums\UserRole;
use App\Models\CompanySetting;
use App\Models\Employee;
use App\Models\QrAttendanceToken;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_can_clock_in_after_valid_qr_scan(): void
    {
        CompanySetting::query()->create([
            'company_name' => 'Test Co',
            'timezone' => 'UTC',
            'allowed_ip_cidrs' => ['127.0.0.1/32'],
            'enforce_company_network' => true,
            'currency' => 'USD',
        ]);

        $user = User::factory()->create([
            'role' => UserRole::Employee,
            'email_verified_at' => now(),
        ]);

        Employee::query()->create([
            'user_id' => $user->id,
            'employee_number' => 'EMP-T1',
            'base_salary' => 1000,
            'status' => EmploymentStatus::Active,
        ]);

        $token = QrAttendanceToken::factory()->create();

        $response = $this->actingAs($user)
            ->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->post(route('portal.attendance.clock-in'), [
                'token' => $token->token,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('attendance_records', [
            'employee_id' => $user->employee->id,
        ]);
    }

    public function test_clock_in_requires_valid_qr_token(): void
    {
        CompanySetting::query()->create([
            'company_name' => 'Test Co',
            'timezone' => 'UTC',
            'allowed_ip_cidrs' => ['127.0.0.1/32'],
            'enforce_company_network' => true,
            'currency' => 'USD',
        ]);

        $user = User::factory()->create([
            'role' => UserRole::Employee,
            'email_verified_at' => now(),
        ]);

        Employee::query()->create([
            'user_id' => $user->id,
            'employee_number' => 'EMP-T3',
            'base_salary' => 1000,
            'status' => EmploymentStatus::Active,
        ]);

        $response = $this->actingAs($user)
            ->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->from(route('portal.attendance.index'))
            ->post(route('portal.attendance.clock-in'));

        $response->assertSessionHasErrors('token');
        $this->assertDatabaseMissing('attendance_records', [
            'employee_id' => $user->fresh()->employee->id,
        ]);
    }

    public function test_scan_verifies_qr_without_clocking_in(): void
    {
        CompanySetting::query()->create([
            'company_name' => 'Test Co',
            'timezone' => 'UTC',
            'allowed_ip_cidrs' => ['127.0.0.1/32'],
            'enforce_company_network' => true,
            'currency' => 'USD',
        ]);

        $user = User::factory()->create([
            'role' => UserRole::Employee,
            'email_verified_at' => now(),
        ]);

        Employee::query()->create([
            'user_id' => $user->id,
            'employee_number' => 'EMP-T4',
            'base_salary' => 1000,
            'status' => EmploymentStatus::Active,
        ]);

        $token = QrAttendanceToken::factory()->create();

        $response = $this->actingAs($user)
            ->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->postJson(route('portal.attendance.scan'), [
                'token' => $token->token,
            ]);

        $response->assertOk()
            ->assertJsonFragment(['message' => 'Station QR verified. You can clock in or out.']);

        $this->assertDatabaseMissing('attendance_records', [
            'employee_id' => $user->employee->id,
        ]);
    }

    public function test_scan_payload_redirects_with_verified_token(): void
    {
        CompanySetting::query()->create([
            'company_name' => 'Test Co',
            'timezone' => 'UTC',
            'allowed_ip_cidrs' => ['127.0.0.1/32'],
            'enforce_company_network' => true,
            'currency' => 'USD',
        ]);

        $user = User::factory()->create([
            'role' => UserRole::Employee,
            'email_verified_at' => now(),
        ]);

        Employee::query()->create([
            'user_id' => $user->id,
            'employee_number' => 'EMP-T5',
            'base_salary' => 1000,
            'status' => EmploymentStatus::Active,
        ]);

        $token = QrAttendanceToken::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('portal.attendance.scan-payload', ['token' => $token->token]));

        $response->assertRedirect(route('portal.attendance.index', [
            'scanned_token' => $token->token,
        ]));
        $response->assertSessionHas('success');
    }

    public function test_attendance_index_marks_valid_scanned_token_as_verified(): void
    {
        CompanySetting::query()->create([
            'company_name' => 'Test Co',
            'timezone' => 'UTC',
            'allowed_ip_cidrs' => ['127.0.0.1/32'],
            'enforce_company_network' => true,
            'currency' => 'USD',
        ]);

        $user = User::factory()->create([
            'role' => UserRole::Employee,
            'email_verified_at' => now(),
        ]);

        Employee::query()->create([
            'user_id' => $user->id,
            'employee_number' => 'EMP-T6',
            'base_salary' => 1000,
            'status' => EmploymentStatus::Active,
        ]);

        $token = QrAttendanceToken::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('portal.attendance.index', [
                'scanned_token' => $token->token,
            ]));

        $response->assertOk();
        $response->assertViewHas('verifiedToken', $token->token);
    }

    public function test_scan_payload_rejects_invalid_token(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::Employee,
            'email_verified_at' => now(),
        ]);

        Employee::query()->create([
            'user_id' => $user->id,
            'employee_number' => 'EMP-T7',
            'base_salary' => 1000,
            'status' => EmploymentStatus::Active,
        ]);

        $response = $this->actingAs($user)
            ->get(route('portal.attendance.scan-payload', ['token' => 'not-a-real-token']));

        $response->assertRedirect(route('portal.attendance.index'));
        $response->assertSessionHas('error');
    }

    public function test_clock_in_is_blocked_off_company_network(): void
    {
        CompanySetting::query()->create([
            'company_name' => 'Test Co',
            'timezone' => 'UTC',
            'allowed_ip_cidrs' => ['10.0.0.0/8'],
            'enforce_company_network' => true,
            'currency' => 'USD',
        ]);

        $user = User::factory()->create([
            'role' => UserRole::Employee,
            'email_verified_at' => now(),
        ]);

        Employee::query()->create([
            'user_id' => $user->id,
            'employee_number' => 'EMP-T2',
            'base_salary' => 1000,
            'status' => EmploymentStatus::Active,
        ]);

        $token = QrAttendanceToken::factory()->create();

        $response = $this->actingAs($user)
            ->withServerVariables(['REMOTE_ADDR' => '203.0.113.10'])
            ->post(route('portal.attendance.clock-in'), [
                'token' => $token->token,
            ]);

        $response->assertForbidden();
    }

    public function test_admin_can_open_employee_index(): void
    {
        $admin = User::factory()->admin()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.employees.index'))
            ->assertOk();
    }
}
