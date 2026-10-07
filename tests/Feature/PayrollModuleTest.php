<?php

namespace Tests\Feature;

use App\Enums\EmploymentStatus;
use App\Enums\LeaveStatus;
use App\Enums\PayrollPeriodStatus;
use App\Enums\PayslipLineType;
use App\Enums\UserRole;
use App\Models\AttendanceRecord;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\PayrollPeriod;
use App\Models\Payslip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PayrollModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_generate_draft_payroll_with_line_items(): void
    {
        $admin = $this->createAdmin();
        $employee = $this->createEmployee([
            'base_salary' => 3000,
            'housing_allowance' => 400,
            'transport_allowance' => 200,
        ]);

        AttendanceRecord::query()->create([
            'employee_id' => $employee->id,
            'work_date' => '2026-03-02',
            'clock_in_at' => '2026-03-02 09:00:00',
            'clock_out_at' => '2026-03-02 19:00:00',
            'worked_minutes' => 600,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.payroll.store'), [
            'name' => 'March 2026',
            'start_date' => '2026-03-01',
            'end_date' => '2026-03-31',
        ]);

        $period = PayrollPeriod::query()->firstOrFail();
        $response->assertRedirect(route('admin.payroll.show', $period));

        $this->assertSame(PayrollPeriodStatus::Draft, $period->fresh()->status);

        $payslip = Payslip::query()->where('employee_id', $employee->id)->firstOrFail();
        $this->assertTrue($payslip->lines()->where('code', 'base_salary')->exists());
        $this->assertTrue($payslip->lines()->where('code', 'housing_allowance')->exists());
        $this->assertTrue($payslip->lines()->where('code', 'transport_allowance')->exists());
        $this->assertTrue($payslip->lines()->where('code', 'overtime')->exists());
        $this->assertGreaterThan(0, (float) $payslip->fresh()->net_pay);
    }

    public function test_manual_adjustment_is_kept_after_regenerate(): void
    {
        $admin = $this->createAdmin();
        $employee = $this->createEmployee(['base_salary' => 4000]);

        $this->actingAs($admin)->post(route('admin.payroll.store'), [
            'name' => 'April 2026',
            'start_date' => '2026-04-01',
            'end_date' => '2026-04-30',
        ]);

        $period = PayrollPeriod::query()->firstOrFail();
        $payslip = Payslip::query()->where('employee_id', $employee->id)->firstOrFail();

        $this->actingAs($admin)->post(route('admin.payroll.payslips.adjustments.store', [$period, $payslip]), [
            'type' => PayslipLineType::Earning->value,
            'label' => 'Performance bonus',
            'amount' => 250,
        ])->assertRedirect();

        $this->assertDatabaseHas('payslip_lines', [
            'payslip_id' => $payslip->id,
            'label' => 'Performance bonus',
            'is_manual' => true,
        ]);

        $netBefore = (float) $payslip->fresh()->net_pay;

        $this->actingAs($admin)
            ->post(route('admin.payroll.regenerate', $period))
            ->assertRedirect(route('admin.payroll.show', $period));

        $regenerated = Payslip::query()->where('employee_id', $employee->id)->firstOrFail();
        $this->assertTrue($regenerated->lines()->where('is_manual', true)->where('label', 'Performance bonus')->exists());
        $this->assertEqualsWithDelta($netBefore, (float) $regenerated->net_pay, 0.01);
    }

    public function test_finalize_locks_payroll_and_exposes_payslip_to_employee(): void
    {
        $admin = $this->createAdmin();
        $employeeUser = User::factory()->create([
            'role' => UserRole::Employee,
            'email_verified_at' => now(),
        ]);
        $employee = Employee::factory()->create([
            'user_id' => $employeeUser->id,
            'base_salary' => 3500,
            'status' => EmploymentStatus::Active,
        ]);

        $this->actingAs($admin)->post(route('admin.payroll.store'), [
            'name' => 'May 2026',
            'start_date' => '2026-05-01',
            'end_date' => '2026-05-31',
        ]);

        $period = PayrollPeriod::query()->firstOrFail();
        $payslip = Payslip::query()->where('employee_id', $employee->id)->firstOrFail();

        $this->actingAs($employeeUser)
            ->get(route('portal.payslips.index'))
            ->assertOk()
            ->assertDontSee($period->name);

        $this->actingAs($admin)
            ->post(route('admin.payroll.finalize', $period))
            ->assertRedirect(route('admin.payroll.show', $period));

        $this->assertSame(PayrollPeriodStatus::Finalized, $period->fresh()->status);

        $this->actingAs($employeeUser)
            ->get(route('portal.payslips.index'))
            ->assertOk()
            ->assertSee($period->name);

        $this->actingAs($employeeUser)
            ->get(route('portal.payslips.print', $payslip))
            ->assertOk()
            ->assertSee(__('Print / Save PDF'));

        $this->actingAs($admin)
            ->post(route('admin.payroll.regenerate', $period))
            ->assertSessionHasErrors('payroll');
    }

    public function test_unpaid_leave_creates_deduction_line(): void
    {
        $admin = $this->createAdmin();
        $employee = $this->createEmployee(['base_salary' => 2200]);

        $unpaid = LeaveType::factory()->create([
            'name' => 'Unpaid Leave',
            'code' => 'UNPAID',
            'is_paid' => false,
        ]);

        LeaveRequest::query()->create([
            'employee_id' => $employee->id,
            'leave_type_id' => $unpaid->id,
            'start_date' => '2026-06-02',
            'end_date' => '2026-06-03',
            'days' => 2,
            'reason' => 'Personal',
            'status' => LeaveStatus::Approved,
        ]);

        $this->actingAs($admin)->post(route('admin.payroll.store'), [
            'name' => 'June 2026',
            'start_date' => '2026-06-01',
            'end_date' => '2026-06-30',
        ]);

        $payslip = Payslip::query()->where('employee_id', $employee->id)->firstOrFail();
        $this->assertTrue($payslip->lines()->where('code', 'unpaid_leave')->exists());
        $this->assertGreaterThan(0, (float) $payslip->deductions);
    }

    private function createAdmin(): User
    {
        return User::factory()->create([
            'role' => UserRole::Admin,
            'email_verified_at' => now(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createEmployee(array $attributes = []): Employee
    {
        $user = User::factory()->create([
            'role' => UserRole::Employee,
            'email_verified_at' => now(),
        ]);

        return Employee::factory()->create([
            'user_id' => $user->id,
            'status' => EmploymentStatus::Active,
            ...$attributes,
        ]);
    }
}
