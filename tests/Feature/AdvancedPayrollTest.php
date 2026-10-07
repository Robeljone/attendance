<?php

namespace Tests\Feature;

use App\Enums\EmploymentStatus;
use App\Enums\PayrollPeriodStatus;
use App\Enums\PayslipLineType;
use App\Enums\UserRole;
use App\Models\CompanySetting;
use App\Models\Employee;
use App\Models\PayComponent;
use App\Models\PayrollPeriod;
use App\Models\Payslip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdvancedPayrollTest extends TestCase
{
    use RefreshDatabase;

    public function test_statutory_deductions_and_workflow_and_bank_export(): void
    {
        CompanySetting::query()->create([
            'company_name' => 'Test Co',
            'timezone' => 'UTC',
            'currency' => 'USD',
            'income_tax_percent' => 10,
            'pension_percent' => 5,
            'allowed_ip_cidrs' => ['127.0.0.1/32'],
            'enforce_company_network' => false,
        ]);

        $admin = $this->createAdmin();
        $employee = $this->createEmployee([
            'base_salary' => 4000,
            'bank_account' => 'BANK-999',
            'housing_allowance' => 0,
            'transport_allowance' => 0,
        ]);

        $this->actingAs($admin)->post(route('admin.payroll.store'), [
            'name' => 'July 2026',
            'start_date' => '2026-07-01',
            'end_date' => '2026-07-31',
        ])->assertRedirect();

        $period = PayrollPeriod::query()->firstOrFail();
        $payslip = Payslip::query()->where('employee_id', $employee->id)->firstOrFail();

        $this->assertTrue($payslip->lines()->where('code', 'income_tax')->exists());
        $this->assertTrue($payslip->lines()->where('code', 'pension')->exists());
        $this->assertSame(PayrollPeriodStatus::Draft, $period->status);

        $this->actingAs($admin)
            ->post(route('admin.payroll.submit', $period))
            ->assertRedirect(route('admin.payroll.show', $period));

        $this->assertSame(PayrollPeriodStatus::PendingApproval, $period->fresh()->status);

        $this->actingAs($admin)
            ->post(route('admin.payroll.finalize', $period))
            ->assertRedirect(route('admin.payroll.show', $period));

        $this->assertSame(PayrollPeriodStatus::Finalized, $period->fresh()->status);

        $csv = $this->actingAs($admin)->get(route('admin.payroll.export-bank-csv', $period));
        $csv->assertOk();
        $csvContent = $csv->streamedContent();
        $this->assertStringContainsString('BANK-999', $csvContent);
        $this->assertStringContainsString($employee->employee_number, $csvContent);

        $this->actingAs($admin)
            ->post(route('admin.payroll.mark-paid', $period))
            ->assertRedirect(route('admin.payroll.show', $period));

        $this->assertSame(PayrollPeriodStatus::Paid, $period->fresh()->status);
    }

    public function test_proration_and_employee_exclusion(): void
    {
        CompanySetting::query()->create([
            'company_name' => 'Test Co',
            'timezone' => 'UTC',
            'currency' => 'USD',
            'income_tax_percent' => 0,
            'pension_percent' => 0,
        ]);

        $admin = $this->createAdmin();
        $included = $this->createEmployee([
            'base_salary' => 3100,
            'hire_date' => '2026-08-17',
        ]);
        $excluded = $this->createEmployee([
            'base_salary' => 5000,
            'hire_date' => '2026-01-01',
        ]);

        $this->actingAs($admin)->post(route('admin.payroll.store'), [
            'name' => 'August 2026',
            'start_date' => '2026-08-01',
            'end_date' => '2026-08-31',
            'excluded_employee_ids' => [$excluded->id],
        ])->assertRedirect();

        $this->assertDatabaseMissing('payslips', ['employee_id' => $excluded->id]);
        $payslip = Payslip::query()->where('employee_id', $included->id)->firstOrFail();

        $this->assertLessThan(3100, (float) $payslip->base_salary);
        $this->assertGreaterThan(0, (float) $payslip->base_salary);
        $this->assertArrayHasKey('proration_factor', $payslip->breakdown);
        $this->assertLessThan(1, (float) $payslip->breakdown['proration_factor']);
    }

    public function test_pay_component_assignment_appears_on_payslip(): void
    {
        CompanySetting::query()->create([
            'company_name' => 'Test Co',
            'timezone' => 'UTC',
            'currency' => 'USD',
            'income_tax_percent' => 0,
            'pension_percent' => 0,
        ]);

        $admin = $this->createAdmin();
        $employee = $this->createEmployee(['base_salary' => 3000]);

        $component = PayComponent::factory()->create([
            'name' => 'Meal allowance',
            'code' => 'meal_allowance',
            'type' => PayslipLineType::Earning,
            'is_taxable' => true,
            'is_active' => true,
        ]);

        $employee->payComponents()->sync([
            $component->id => ['amount' => 150, 'is_enabled' => true],
        ]);

        $this->actingAs($admin)->post(route('admin.payroll.store'), [
            'name' => 'September 2026',
            'start_date' => '2026-09-01',
            'end_date' => '2026-09-30',
        ])->assertRedirect();

        $payslip = Payslip::query()->where('employee_id', $employee->id)->firstOrFail();
        $this->assertTrue($payslip->lines()->where('code', 'meal_allowance')->where('amount', 150)->exists());
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
