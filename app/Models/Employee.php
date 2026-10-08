<?php

namespace App\Models;

use App\Enums\EmploymentStatus;
use App\Enums\Gender;
use App\Enums\MaritalStatus;
use Database\Factories\EmployeeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Employee extends Model
{
    /** @use HasFactory<EmployeeFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'department_id',
        'salary_structure_id',
        'employee_number',
        'phone',
        'position',
        'hire_date',
        'termination_date',
        'date_of_birth',
        'address',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relationship',
        'base_salary',
        'housing_allowance',
        'transport_allowance',
        'bank_account',
        'status',
        'notes',
        'photo_path',
        'gender',
        'marital_status',
        'nationality',
        'national_id',
        'tax_id',
        'personal_email',
        'blood_group',
    ];

    protected function casts(): array
    {
        return [
            'hire_date' => 'date',
            'termination_date' => 'date',
            'date_of_birth' => 'date',
            'base_salary' => 'decimal:2',
            'housing_allowance' => 'decimal:2',
            'transport_allowance' => 'decimal:2',
            'status' => EmploymentStatus::class,
            'gender' => Gender::class,
            'marital_status' => MaritalStatus::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function salaryStructure(): BelongsTo
    {
        return $this->belongsTo(SalaryStructure::class);
    }

    public function workSchedules(): BelongsToMany
    {
        return $this->belongsToMany(WorkSchedule::class)
            ->withPivot(['effective_from', 'effective_to'])
            ->withTimestamps();
    }

    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(EmployeeAuditLog::class);
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function expenseClaims(): HasMany
    {
        return $this->hasMany(ExpenseClaim::class);
    }

    public function salaryIncrements(): HasMany
    {
        return $this->hasMany(SalaryIncrement::class);
    }

    public function payslips(): HasMany
    {
        return $this->hasMany(Payslip::class);
    }

    public function payComponents(): BelongsToMany
    {
        return $this->belongsToMany(PayComponent::class)
            ->withPivot(['amount', 'is_enabled'])
            ->withTimestamps()
            ->orderBy('pay_components.sort_order');
    }

    public function educations(): HasMany
    {
        return $this->hasMany(EmployeeEducation::class)->orderByDesc('is_highest')->orderByDesc('end_year');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(EmployeeDocument::class)->latest();
    }

    public function photoUrl(): ?string
    {
        if (! filled($this->photo_path)) {
            return null;
        }

        return Storage::disk('public')->url($this->photo_path);
    }

    public function deletePhoto(): void
    {
        if (filled($this->photo_path)) {
            Storage::disk('public')->delete($this->photo_path);
        }
    }

    public function currentSchedule(): ?WorkSchedule
    {
        return $this->workSchedules()
            ->where(function ($query): void {
                $query->whereNull('employee_work_schedule.effective_to')
                    ->orWhere('employee_work_schedule.effective_to', '>=', now()->toDateString());
            })
            ->orderByDesc('employee_work_schedule.effective_from')
            ->first();
    }
}
