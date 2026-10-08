<?php

namespace Tests\Feature;

use App\Enums\BonusRunStatus;
use App\Enums\EmploymentStatus;
use App\Enums\ExpenseClaimStatus;
use App\Enums\PayslipLineType;
use App\Enums\SalaryIncrementStatus;
use App\Enums\SalaryIncrementType;
use App\Enums\UserRole;
use App\Models\BonusRun;
use App\Models\CompanySetting;
use App\Models\Employee;
use App\Models\ExpenseClaim;
use App\Models\PayComponent;
use App\Models\PayrollPeriod;
use App\Models\Payslip;
use App\Models\SalaryIncrement;
use App\Models\SalaryStructure;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinanceModulesTest extends TestCase
{
    use RefreshDatabase;

    public function test_salary_structure_applies_components_to_employee(): void
    {
        $this->seedSettings();
        $admin = $this->createAdmin();
        $employee = $this->createEmployee(['base_salary' => 3000]);

        $meal = PayComponent::factory()->create([
            'name' => 'Meal',
            'code' => 'meal_structure_test',
            'type' => PayslipLineType::Earning,
            'default_amount' => 200,
            'is_active' => true,
        ]);

        $this->actingAs($admin)->post(route('admin.salary-structures.store'), [
            'name' => 'Standard staff',
            'code' => 'standard_staff',
            'is_active' => 1,
            'components' => [
                $meal->id => ['enabled' => 1],
            ],
        ])->assertRedirect(route('admin.salary-structures.index'));

        $structure = SalaryStructure::query()->firstOrFail();

        $this->actingAs($admin)
            ->post(route('admin.salary-structures.apply', $structure), [
                'employee_id' => $employee->id,
            ])
            ->assertRedirect();

        $employee->refresh();
        $this->assertSame($structure->id, $employee->salary_structure_id);
        $this->assertTrue($employee->payComponents()->where('pay_components.id', $meal->id)->exists());
        $this->assertEquals(200.0, (float) $employee->payComponents()->where('pay_components.id', $meal->id)->first()->pivot->amount);
    }

    public function test_bonus_run_and_expense_claim_appear_on_payroll(): void
    {
        $this->seedSettings();
        $admin = $this->createAdmin();
        $employee = $this->createEmployee([
            'base_salary' => 4000,
            'housing_allowance' => 0,
            'transport_allowance' => 0,
        ]);

        $this->actingAs($admin)->post(route('admin.payroll.store'), [
            'name' => 'Oct 2026',
            'start_date' => '2026-10-01',
            'end_date' => '2026-10-31',
        ])->assertRedirect();

        $period = PayrollPeriod::query()->firstOrFail();

        $this->actingAs($admin)->post(route('admin.bonus-runs.store'), [
            'name' => 'Q4 bonus',
            'payroll_period_id' => $period->id,
            'items' => [
                ['employee_id' => $employee->id, 'amount' => 500, 'label' => 'Performance'],
            ],
        ])->assertRedirect(route('admin.bonus-runs.index'));

        $runId = BonusRun::query()->firstOrFail()->id;

        $this->actingAs($admin)
            ->post(route('admin.bonus-runs.apply', $runId))
            ->assertRedirect();

        $this->assertSame(BonusRunStatus::Applied, BonusRun::query()->firstOrFail()->status);

        $claim = ExpenseClaim::factory()->create([
            'employee_id' => $employee->id,
            'amount' => 75,
            'expense_date' => '2026-10-15',
            'status' => ExpenseClaimStatus::Pending,
            'title' => 'Taxi',
            'category' => 'travel',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.expense-claims.approve', $claim))
            ->assertRedirect();

        $this->actingAs($admin)
            ->post(route('admin.payroll.regenerate', $period))
            ->assertRedirect();

        $payslip = Payslip::query()->where('employee_id', $employee->id)->firstOrFail();
        $this->assertTrue($payslip->lines()->where('code', 'bonus_run_'.$runId)->where('amount', 500)->exists());
        $this->assertTrue($payslip->lines()->where('code', 'expense_claim_'.$claim->id)->where('amount', 75)->exists());
        $this->assertSame(ExpenseClaimStatus::Paid, $claim->fresh()->status);
    }

    public function test_salary_increment_updates_base_salary(): void
    {
        $this->seedSettings();
        $admin = $this->createAdmin();
        $employee = $this->createEmployee(['base_salary' => 2000]);

        $this->actingAs($admin)->post(route('admin.salary-increments.store'), [
            'employee_id' => $employee->id,
            'type' => SalaryIncrementType::Fixed->value,
            'amount' => 200,
            'effective_date' => now()->toDateString(),
        ])->assertRedirect(route('admin.salary-increments.index'));

        $increment = SalaryIncrement::query()->firstOrFail();

        $this->actingAs($admin)
            ->post(route('admin.salary-increments.apply', $increment))
            ->assertRedirect();

        $this->assertSame(SalaryIncrementStatus::Applied, $increment->fresh()->status);
        $this->assertEquals(2200.0, (float) $employee->fresh()->base_salary);
    }

    public function test_attendance_pay_rules_can_be_saved(): void
    {
        $this->seedSettings();
        $admin = $this->createAdmin();

        $this->actingAs($admin)->put(route('admin.settings.update'), [
            'company_name' => 'Test Co',
            'timezone' => 'UTC',
            'currency' => 'USD',
            'income_tax_percent' => 0,
            'pension_percent' => 0,
            'standard_work_hours_per_day' => 8,
            'overtime_weekday_multiplier' => 1.5,
            'overtime_weekend_multiplier' => 2,
            'late_grace_minutes' => 10,
            'late_penalty_per_occurrence' => 25,
            'deduct_unexcused_absence' => 1,
            'allowed_ip_cidrs' => '127.0.0.1/32',
        ])->assertRedirect();

        $settings = CompanySetting::current();
        $this->assertEquals(10, (int) $settings->late_grace_minutes);
        $this->assertEquals(25.0, (float) $settings->late_penalty_per_occurrence);
        $this->assertTrue((bool) $settings->deduct_unexcused_absence);
    }

    public function test_finance_reports_page_loads(): void
    {
        $this->seedSettings();
        $admin = $this->createAdmin();

        $this->actingAs($admin)
            ->get(route('admin.reports.index'))
            ->assertOk()
            ->assertSee('Finance payroll summary')
            ->assertSee('Payroll by department')
            ->assertSee('Deduction breakdown')
            ->assertSee('Monthly payroll trend');
    }

    private function seedSettings(): void
    {
        CompanySetting::query()->create([
            'company_name' => 'Test Co',
            'timezone' => 'UTC',
            'currency' => 'USD',
            'income_tax_percent' => 0,
            'pension_percent' => 0,
            'allowed_ip_cidrs' => ['127.0.0.1/32'],
            'enforce_company_network' => false,
        ]);
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
