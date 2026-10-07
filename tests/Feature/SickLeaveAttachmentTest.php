<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SickLeaveAttachmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_sick_leave_requires_attachment(): void
    {
        $user = User::factory()->create(['role' => UserRole::Employee]);
        Employee::factory()->create(['user_id' => $user->id]);
        $sick = LeaveType::factory()->sick()->create();

        $this->actingAs($user)
            ->from(route('portal.leaves.index'))
            ->post(route('portal.leaves.store'), [
                'form_modal' => 'create-leave',
                'leave_type_id' => $sick->id,
                'start_date' => now()->toDateString(),
                'end_date' => now()->addDay()->toDateString(),
                'reason' => 'Fever',
            ])
            ->assertRedirect(route('portal.leaves.index'))
            ->assertSessionHasErrors('attachment');

        $this->assertDatabaseCount('leave_requests', 0);
    }

    public function test_sick_leave_can_be_submitted_with_attachment(): void
    {
        Storage::fake('local');

        $user = User::factory()->create(['role' => UserRole::Employee]);
        Employee::factory()->create(['user_id' => $user->id]);
        $sick = LeaveType::factory()->sick()->create();
        $file = UploadedFile::fake()->create('doctor-note.pdf', 100, 'application/pdf');

        $this->actingAs($user)
            ->post(route('portal.leaves.store'), [
                'form_modal' => 'create-leave',
                'leave_type_id' => $sick->id,
                'start_date' => now()->toDateString(),
                'end_date' => now()->addDay()->toDateString(),
                'reason' => 'Fever',
                'attachment' => $file,
            ])
            ->assertRedirect(route('portal.leaves.index'))
            ->assertSessionHas('success');

        $leave = LeaveRequest::query()->first();
        $this->assertNotNull($leave);
        $this->assertNotNull($leave->attachment_path);
        Storage::disk('local')->assertExists($leave->attachment_path);
    }

    public function test_annual_leave_does_not_require_attachment(): void
    {
        $user = User::factory()->create(['role' => UserRole::Employee]);
        Employee::factory()->create(['user_id' => $user->id]);
        $annual = LeaveType::factory()->create([
            'name' => 'Annual Leave',
            'code' => 'ANNUAL',
        ]);

        $this->actingAs($user)
            ->post(route('portal.leaves.store'), [
                'form_modal' => 'create-leave',
                'leave_type_id' => $annual->id,
                'start_date' => now()->toDateString(),
                'end_date' => now()->addDays(2)->toDateString(),
                'reason' => 'Family trip',
            ])
            ->assertRedirect(route('portal.leaves.index'))
            ->assertSessionHas('success')
            ->assertSessionDoesntHaveErrors();

        $this->assertDatabaseCount('leave_requests', 1);
    }

    public function test_leave_form_mentions_medical_attachment_for_sick_leave(): void
    {
        $user = User::factory()->create(['role' => UserRole::Employee]);
        Employee::factory()->create(['user_id' => $user->id]);
        LeaveType::factory()->sick()->create();

        $this->actingAs($user)
            ->get(route('portal.leaves.index', ['modal' => 'create']))
            ->assertOk()
            ->assertSee('name="attachment"', false)
            ->assertSee('Medical attachment');
    }
}
