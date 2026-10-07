<?php

namespace App\Http\Controllers\Admin;

use App\Enums\LeaveStatus;
use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Support\ResolvesIndexPagination;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LeaveRequestController extends Controller
{
    public function index(Request $request): View
    {
        $search = ResolvesIndexPagination::search($request);

        $leaves = LeaveRequest::query()
            ->with(['employee.user', 'leaveType', 'reviewer'])
            ->when($search, function ($query) use ($search): void {
                $term = '%'.$search.'%';
                $query->where(function ($inner) use ($term): void {
                    $inner->whereHas('employee.user', fn ($user) => $user->where('name', 'like', $term))
                        ->orWhereHas('leaveType', fn ($type) => $type->where('name', 'like', $term));
                });
            })
            ->latest()
            ->paginate(ResolvesIndexPagination::perPage($request, 20))
            ->withQueryString();

        return view('admin.leaves.index', ['leaveRequests' => $leaves]);
    }

    public function approve(Request $request, LeaveRequest $leave): RedirectResponse
    {
        if ($leave->status !== LeaveStatus::Pending) {
            return back()->with('error', 'Only pending leave requests can be approved.');
        }

        $leave->update([
            'status' => LeaveStatus::Approved,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
            'review_notes' => $request->input('review_notes'),
        ]);

        return back()->with('success', 'Leave request approved.');
    }

    public function reject(Request $request, LeaveRequest $leave): RedirectResponse
    {
        if ($leave->status !== LeaveStatus::Pending) {
            return back()->with('error', 'Only pending leave requests can be rejected.');
        }

        $leave->update([
            'status' => LeaveStatus::Rejected,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
            'review_notes' => $request->input('review_notes'),
        ]);

        return back()->with('success', 'Leave request rejected.');
    }

    public function downloadAttachment(LeaveRequest $leave): StreamedResponse
    {
        abort_unless($leave->hasAttachment() && Storage::disk('local')->exists($leave->attachment_path), 404);

        return Storage::disk('local')->download(
            $leave->attachment_path,
            $leave->attachment_original_name ?: 'leave-attachment',
        );
    }
}
