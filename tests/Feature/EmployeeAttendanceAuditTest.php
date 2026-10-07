<?php

namespace Tests\Feature;

use App\Enums\EmployeeAuditAction;
use App\Enums\EmployeeAuditOutcome;
use App\Enums\EmploymentStatus;
use App\Enums\UserRole;
use App\Models\CompanySetting;
use App\Models\Employee;
use App\Models\EmployeeAuditLog;
use App\Models\QrAttendanceToken;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeAttendanceAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_successful_clock_in_is_audited_with_allowlisted_network(): void
    {
        $this->configureNetwork(['127.0.0.1/32'], true);

        $user = $this->createEmployeeUser('EMP-A1');
        $token = QrAttendanceToken::factory()->create(['station_name' => 'Lobby']);

        $this->actingAs($user)
            ->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->post(route('portal.attendance.clock-in'), [
                'token' => $token->token,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('employee_audit_logs', [
            'employee_id' => $user->employee->id,
            'user_id' => $user->id,
            'action' => EmployeeAuditAction::ClockIn->value,
            'outcome' => EmployeeAuditOutcome::Success->value,
            'ip_address' => '127.0.0.1',
            'network_allowed' => true,
            'network_enforced' => true,
        ]);
    }

    public function test_off_network_clock_in_is_audited_as_blocked(): void
    {
        $this->configureNetwork(['10.0.0.0/8'], true);

        $user = $this->createEmployeeUser('EMP-A2');
        $token = QrAttendanceToken::factory()->create();

        $this->actingAs($user)
            ->withServerVariables(['REMOTE_ADDR' => '203.0.113.10'])
            ->post(route('portal.attendance.clock-in'), [
                'token' => $token->token,
            ])
            ->assertForbidden();

        $this->assertDatabaseHas('employee_audit_logs', [
            'employee_id' => $user->employee->id,
            'action' => EmployeeAuditAction::AttendanceBlocked->value,
            'outcome' => EmployeeAuditOutcome::Blocked->value,
            'ip_address' => '203.0.113.10',
            'network_allowed' => false,
            'network_enforced' => true,
        ]);
    }

    public function test_invalid_qr_scan_is_audited(): void
    {
        $this->configureNetwork(['127.0.0.1/32'], true);

        $user = $this->createEmployeeUser('EMP-A3');

        $this->actingAs($user)
            ->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->postJson(route('portal.attendance.scan'), [
                'token' => 'expired-or-fake',
            ])
            ->assertStatus(422);

        $this->assertDatabaseHas('employee_audit_logs', [
            'employee_id' => $user->employee->id,
            'action' => EmployeeAuditAction::QrScan->value,
            'outcome' => EmployeeAuditOutcome::Failed->value,
            'network_allowed' => true,
        ]);
    }

    public function test_admin_can_view_employee_attendance_audit_report(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin,
            'email_verified_at' => now(),
        ]);

        $employeeUser = $this->createEmployeeUser('EMP-A4');

        EmployeeAuditLog::factory()->blocked()->create([
            'employee_id' => $employeeUser->employee->id,
            'user_id' => $employeeUser->id,
            'ip_address' => '203.0.113.44',
            'message' => 'Blocked: not on company network.',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.reports.audit', [
                'network' => 'not_allowed',
            ]))
            ->assertOk()
            ->assertSee('203.0.113.44')
            ->assertSee('Not allowlisted')
            ->assertSee($employeeUser->name);
    }

    public function test_employee_cannot_view_attendance_audit_report(): void
    {
        $user = $this->createEmployeeUser('EMP-A5');

        $this->actingAs($user)
            ->get(route('admin.reports.audit'))
            ->assertForbidden();
    }

    /**
     * @param  list<string>  $cidrs
     */
    private function configureNetwork(array $cidrs, bool $enforce): void
    {
        CompanySetting::query()->create([
            'company_name' => 'Test Co',
            'timezone' => 'UTC',
            'currency' => 'USD',
            'allowed_ip_cidrs' => $cidrs,
            'enforce_company_network' => $enforce,
        ]);
    }

    private function createEmployeeUser(string $employeeNumber): User
    {
        $user = User::factory()->create([
            'role' => UserRole::Employee,
            'email_verified_at' => now(),
        ]);

        Employee::query()->create([
            'user_id' => $user->id,
            'employee_number' => $employeeNumber,
            'base_salary' => 1000,
            'status' => EmploymentStatus::Active,
        ]);

        return $user->fresh(['employee']);
    }
}
