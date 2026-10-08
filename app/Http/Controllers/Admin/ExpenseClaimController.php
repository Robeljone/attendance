<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ExpenseClaimStatus;
use App\Http\Controllers\Controller;
use App\Models\ExpenseClaim;
use App\Support\ResolvesIndexPagination;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExpenseClaimController extends Controller
{
    public function index(Request $request): View
    {
        $search = ResolvesIndexPagination::search($request);

        $claims = ExpenseClaim::query()
            ->with(['employee.user', 'reviewer'])
            ->when($search, function ($query) use ($search): void {
                $term = '%'.$search.'%';
                $query->where(function ($inner) use ($term): void {
                    $inner->where('title', 'like', $term)
                        ->orWhere('category', 'like', $term)
                        ->orWhereHas('employee.user', fn ($user) => $user->where('name', 'like', $term));
                });
            })
            ->when($request->filled('status'), function ($query) use ($request): void {
                $status = ExpenseClaimStatus::tryFrom((string) $request->string('status'));
                if ($status) {
                    $query->where('status', $status);
                }
            })
            ->latest()
            ->paginate(ResolvesIndexPagination::perPage($request, 20))
            ->withQueryString();

        return view('admin.expense-claims.index', [
            'claims' => $claims,
            'statuses' => ExpenseClaimStatus::cases(),
        ]);
    }

    public function approve(Request $request, ExpenseClaim $expenseClaim): RedirectResponse
    {
        if ($expenseClaim->status !== ExpenseClaimStatus::Pending) {
            return back()->with('error', __('Only pending claims can be approved.'));
        }

        $expenseClaim->update([
            'status' => ExpenseClaimStatus::Approved,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
            'review_notes' => $request->input('review_notes'),
        ]);

        return back()->with('success', __('Expense claim approved. It will be paid on the next matching payroll.'));
    }

    public function reject(Request $request, ExpenseClaim $expenseClaim): RedirectResponse
    {
        if ($expenseClaim->status !== ExpenseClaimStatus::Pending) {
            return back()->with('error', __('Only pending claims can be rejected.'));
        }

        $expenseClaim->update([
            'status' => ExpenseClaimStatus::Rejected,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
            'review_notes' => $request->input('review_notes'),
        ]);

        return back()->with('success', __('Expense claim rejected.'));
    }

    public function downloadReceipt(ExpenseClaim $expenseClaim): StreamedResponse
    {
        abort_unless($expenseClaim->hasReceipt() && Storage::disk('local')->exists($expenseClaim->receipt_path), 404);

        return Storage::disk('local')->download(
            $expenseClaim->receipt_path,
            $expenseClaim->receipt_original_name ?: 'expense-receipt',
        );
    }
}
