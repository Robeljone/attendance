<?php

use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;
use App\Http\Controllers\Admin\BrandingController;
use App\Http\Controllers\Admin\CompanySettingController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\EmployeeDocumentController;
use App\Http\Controllers\Admin\EmployeeEducationController;
use App\Http\Controllers\Admin\LeaveRequestController as AdminLeaveRequestController;
use App\Http\Controllers\Admin\PayrollController;
use App\Http\Controllers\Admin\QrStationController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\WorkScheduleController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Portal\AttendanceController as PortalAttendanceController;
use App\Http\Controllers\Portal\LeaveRequestController as PortalLeaveRequestController;
use App\Http\Controllers\Portal\PayslipController;
use App\Http\Controllers\Portal\ProfileController as PortalProfileController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::prefix('portal')->name('portal.')->group(function () {
        Route::get('/attendance', [PortalAttendanceController::class, 'index'])->name('attendance.index');
        Route::get('/attendance/scan/{token}', [PortalAttendanceController::class, 'scanPayload'])->name('attendance.scan-payload');

        Route::middleware('company.network')->group(function () {
            Route::post('/attendance/clock-in', [PortalAttendanceController::class, 'clockIn'])->name('attendance.clock-in');
            Route::post('/attendance/clock-out', [PortalAttendanceController::class, 'clockOut'])->name('attendance.clock-out');
            Route::post('/attendance/scan', [PortalAttendanceController::class, 'scan'])->name('attendance.scan');
        });

        Route::get('/leaves', [PortalLeaveRequestController::class, 'index'])->name('leaves.index');
        Route::get('/leaves/create', [PortalLeaveRequestController::class, 'create'])->name('leaves.create');
        Route::post('/leaves', [PortalLeaveRequestController::class, 'store'])->name('leaves.store');
        Route::get('/leaves/{leave}/attachment', [PortalLeaveRequestController::class, 'downloadAttachment'])->name('leaves.attachment');

        Route::get('/payslips', [PayslipController::class, 'index'])->name('payslips.index');
        Route::get('/payslips/{payslip}', [PayslipController::class, 'show'])->name('payslips.show');

        Route::get('/my-info', [PortalProfileController::class, 'show'])->name('profile.show');
    });

    Route::prefix('admin')->name('admin.')->middleware('role:superadmin,admin,hr')->group(function () {
        Route::resource('employees', EmployeeController::class);
        Route::post('employees/{employee}/educations', [EmployeeEducationController::class, 'store'])->name('employees.educations.store');
        Route::put('employees/{employee}/educations/{education}', [EmployeeEducationController::class, 'update'])->name('employees.educations.update');
        Route::delete('employees/{employee}/educations/{education}', [EmployeeEducationController::class, 'destroy'])->name('employees.educations.destroy');
        Route::post('employees/{employee}/documents', [EmployeeDocumentController::class, 'store'])->name('employees.documents.store');
        Route::get('employees/{employee}/documents/{document}/download', [EmployeeDocumentController::class, 'download'])->name('employees.documents.download');
        Route::delete('employees/{employee}/documents/{document}', [EmployeeDocumentController::class, 'destroy'])->name('employees.documents.destroy');
        Route::resource('departments', DepartmentController::class)->except(['show']);
        Route::resource('schedules', WorkScheduleController::class)
            ->parameters(['schedules' => 'schedule'])
            ->except(['show']);

        Route::get('/attendance', [AdminAttendanceController::class, 'index'])->name('attendance.index');

        Route::get('/leaves', [AdminLeaveRequestController::class, 'index'])->name('leaves.index');
        Route::post('/leaves/{leave}/approve', [AdminLeaveRequestController::class, 'approve'])->name('leaves.approve');
        Route::post('/leaves/{leave}/reject', [AdminLeaveRequestController::class, 'reject'])->name('leaves.reject');
        Route::get('/leaves/{leave}/attachment', [AdminLeaveRequestController::class, 'downloadAttachment'])->name('leaves.attachment');

        Route::get('/payroll', [PayrollController::class, 'index'])->name('payroll.index');
        Route::get('/payroll/create', [PayrollController::class, 'create'])->name('payroll.create');
        Route::post('/payroll', [PayrollController::class, 'store'])->name('payroll.store');
        Route::get('/payroll/{payroll}', [PayrollController::class, 'show'])->name('payroll.show');

        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

        Route::get('/settings', [CompanySettingController::class, 'edit'])->name('settings.edit');
        Route::put('/settings', [CompanySettingController::class, 'update'])->name('settings.update');

        Route::middleware('role:superadmin')->group(function () {
            Route::get('/branding', [BrandingController::class, 'edit'])->name('branding.edit');
            Route::put('/branding', [BrandingController::class, 'update'])->name('branding.update');
        });

        Route::get('/qr-station', [QrStationController::class, 'station'])->name('qr.station');
        Route::get('/qr-token', [QrStationController::class, 'token'])->name('qr.token');
    });
});

require __DIR__.'/auth.php';
