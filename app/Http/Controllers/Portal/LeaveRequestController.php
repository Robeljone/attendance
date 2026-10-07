<?php

namespace App\Http\Controllers\Portal;

use App\Enums\LeaveStatus;
use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LeaveRequestController extends Controller
{
    public function index(Request $request): View
    {
        $employee = $request->user()->employee;
        abort_unless($employee, 403);

        $leaves = LeaveRequest::query()
            ->with('leaveType')
            ->where('employee_id', $employee->id)
            ->latest()
            ->paginate(15);

        return view('portal.leaves.index', [
            'leaveRequests' => $leaves,
            'leaveTypes' => LeaveType::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function create(Request $request): RedirectResponse
    {
        abort_unless($request->user()->employee, 403);

        return redirect()->route('portal.leaves.index', ['modal' => 'create']);
    }

    public function store(Request $request): RedirectResponse
    {
        $employee = $request->user()->employee;
        abort_unless($employee, 403);

        $sickTypeIds = LeaveType::query()
            ->where('is_active', true)
            ->whereRaw('upper(code) = ?', ['SICK'])
            ->pluck('id')
            ->all();

        $validated = $request->validate([
            'leave_type_id' => ['required', 'exists:leave_types,id'],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['nullable', 'string', 'max:1000'],
            'attachment' => [
                Rule::requiredIf(fn () => in_array((int) $request->input('leave_type_id'), $sickTypeIds, true)),
                'nullable',
                'file',
                'mimes:jpg,jpeg,png,webp,pdf',
                'max:5120',
            ],
        ]);

        $days = Carbon::parse($validated['start_date'])
            ->diffInDays(Carbon::parse($validated['end_date'])) + 1;

        $attachmentPath = null;
        $attachmentOriginalName = null;

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $attachmentPath = $file->store('leave-attachments/'.$employee->id, 'local');
            $attachmentOriginalName = $file->getClientOriginalName();
        }

        LeaveRequest::query()->create([
            'employee_id' => $employee->id,
            'leave_type_id' => $validated['leave_type_id'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'days' => $days,
            'reason' => $validated['reason'] ?? null,
            'attachment_path' => $attachmentPath,
            'attachment_original_name' => $attachmentOriginalName,
            'status' => LeaveStatus::Pending,
        ]);

        return redirect()->route('portal.leaves.index')->with('success', 'Leave request submitted.');
    }

    public function downloadAttachment(Request $request, LeaveRequest $leave): StreamedResponse
    {
        $employee = $request->user()->employee;
        abort_unless($employee && $leave->employee_id === $employee->id, 403);
        abort_unless($leave->hasAttachment() && Storage::disk('local')->exists($leave->attachment_path), 404);

        return Storage::disk('local')->download(
            $leave->attachment_path,
            $leave->attachment_original_name ?: 'leave-attachment',
        );
    }
}
