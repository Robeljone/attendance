<?php

namespace App\Http\Controllers\Portal;

use App\Enums\ExpenseClaimStatus;
use App\Http\Controllers\Controller;
use App\Models\ExpenseClaim;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExpenseClaimController extends Controller
{
    /** @var list<string> */
    public const CATEGORIES = [
        'travel',
        'meals',
        'medical',
        'office',
        'other',
    ];

    public function index(Request $request): View
    {
        $employee = $request->user()->employee;
        abort_unless($employee, 403);

        $claims = ExpenseClaim::query()
            ->where('employee_id', $employee->id)
            ->latest()
            ->paginate(15);

        return view('portal.expense-claims.index', [
            'claims' => $claims,
            'categories' => self::CATEGORIES,
        ]);
    }

    public function create(Request $request): RedirectResponse
    {
        abort_unless($request->user()->employee, 403);

        return redirect()->route('portal.expense-claims.index', ['modal' => 'create']);
    }

    public function store(Request $request): RedirectResponse
    {
        $employee = $request->user()->employee;
        abort_unless($employee, 403);

        $validated = $request->validate([
            'category' => ['required', Rule::in(self::CATEGORIES)],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'expense_date' => ['required', 'date', 'before_or_equal:today'],
            'receipt' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
        ]);

        $receiptPath = null;
        $receiptOriginalName = null;

        if ($request->hasFile('receipt')) {
            $file = $request->file('receipt');
            $receiptPath = $file->store('expense-receipts/'.$employee->id, 'local');
            $receiptOriginalName = $file->getClientOriginalName();
        }

        ExpenseClaim::query()->create([
            'employee_id' => $employee->id,
            'category' => $validated['category'],
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'amount' => $validated['amount'],
            'expense_date' => $validated['expense_date'],
            'receipt_path' => $receiptPath,
            'receipt_original_name' => $receiptOriginalName,
            'status' => ExpenseClaimStatus::Pending,
        ]);

        return redirect()->route('portal.expense-claims.index')->with('success', __('Expense claim submitted.'));
    }

    public function downloadReceipt(Request $request, ExpenseClaim $expenseClaim): StreamedResponse
    {
        $employee = $request->user()->employee;
        abort_unless($employee && $expenseClaim->employee_id === $employee->id, 403);
        abort_unless($expenseClaim->hasReceipt() && Storage::disk('local')->exists($expenseClaim->receipt_path), 404);

        return Storage::disk('local')->download(
            $expenseClaim->receipt_path,
            $expenseClaim->receipt_original_name ?: 'expense-receipt',
        );
    }
}
