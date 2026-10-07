<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Support\ResolvesIndexPagination;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $search = ResolvesIndexPagination::search($request);

        $records = AttendanceRecord::query()
            ->with(['employee.user', 'employee.department'])
            ->when($request->filled('from'), fn ($q) => $q->whereDate('work_date', '>=', $request->date('from')))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('work_date', '<=', $request->date('to')))
            ->when($search, function ($q) use ($search): void {
                $term = '%'.$search.'%';
                $q->whereHas('employee', function ($employee) use ($term): void {
                    $employee->where('employee_number', 'like', $term)
                        ->orWhereHas('user', fn ($user) => $user->where('name', 'like', $term));
                });
            })
            ->latest('work_date')
            ->paginate(ResolvesIndexPagination::perPage($request, 20))
            ->withQueryString();

        return view('admin.attendance.index', ['attendanceRecords' => $records]);
    }
}
