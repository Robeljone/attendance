<?php

namespace App\Http\Controllers;

use App\Enums\EmploymentStatus;
use App\Enums\LeaveStatus;
use App\Models\AttendanceRecord;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\PayrollPeriod;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $stats = [];

        if ($user->canManageHr()) {
            $stats = [
                'employees' => Employee::query()->where('status', EmploymentStatus::Active)->count(),
                'present_today' => AttendanceRecord::query()->whereDate('work_date', today())->whereNotNull('clock_in_at')->count(),
                'pending_leaves' => LeaveRequest::query()->where('status', LeaveStatus::Pending)->count(),
                'payroll_periods' => PayrollPeriod::query()->count(),
            ];
        } elseif ($user->employee) {
            $today = AttendanceRecord::query()
                ->where('employee_id', $user->employee->id)
                ->whereDate('work_date', today())
                ->first();

            $stats = [
                'clocked_in' => (bool) $today?->clock_in_at,
                'clocked_out' => (bool) $today?->clock_out_at,
                'pending_leaves' => LeaveRequest::query()
                    ->where('employee_id', $user->employee->id)
                    ->where('status', LeaveStatus::Pending)
                    ->count(),
                'worked_minutes' => $today?->worked_minutes ?? 0,
            ];
        }

        return view('dashboard', [
            'stats' => $stats,
        ]);
    }
}
