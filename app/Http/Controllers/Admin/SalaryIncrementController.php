<?php

namespace App\Http\Controllers\Admin;

use App\Actions\ApplySalaryIncrementAction;
use App\Enums\EmploymentStatus;
use App\Enums\SalaryIncrementStatus;
use App\Enums\SalaryIncrementType;
use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\SalaryIncrement;
use App\Support\ResolvesIndexPagination;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use InvalidArgumentException;

class SalaryIncrementController extends Controller
{
    public function index(Request $request): View
    {
        $search = ResolvesIndexPagination::search($request);

        $increments = SalaryIncrement::query()
            ->with(['employee.user', 'creator'])
            ->when($search, function ($query) use ($search): void {
                $query->whereHas('employee.user', fn ($user) => $user->where('name', 'like', '%'.$search.'%'));
            })
            ->latest()
            ->paginate(ResolvesIndexPagination::perPage($request))
            ->withQueryString();

        return view('admin.salary-increments.index', [
            'increments' => $increments,
            'employees' => Employee::query()
                ->with('user')
                ->where('status', EmploymentStatus::Active)
                ->orderBy('employee_number')
                ->get(),
            'types' => SalaryIncrementType::cases(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'type' => ['required', Rule::enum(SalaryIncrementType::class)],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'effective_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        SalaryIncrement::query()->create([
            ...$validated,
            'status' => SalaryIncrementStatus::Pending,
            'created_by' => $request->user()->id,
        ]);

        return redirect()->route('admin.salary-increments.index')->with('success', __('Salary increment scheduled.'));
    }

    public function apply(Request $request, SalaryIncrement $salaryIncrement, ApplySalaryIncrementAction $action): RedirectResponse
    {
        try {
            $action->handle($salaryIncrement, $request->user());
        } catch (InvalidArgumentException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', __('Salary increment applied.'));
    }

    public function destroy(SalaryIncrement $salaryIncrement): RedirectResponse
    {
        if (! $salaryIncrement->isPending()) {
            return back()->with('error', __('Only pending increments can be deleted.'));
        }

        $salaryIncrement->delete();

        return back()->with('success', __('Salary increment deleted.'));
    }
}
