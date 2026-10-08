<?php

namespace App\Http\Controllers\Admin;

use App\Actions\ApplyBonusRunAction;
use App\Enums\BonusRunStatus;
use App\Enums\EmploymentStatus;
use App\Enums\PayrollPeriodStatus;
use App\Http\Controllers\Controller;
use App\Models\BonusRun;
use App\Models\Employee;
use App\Models\PayrollPeriod;
use App\Support\ResolvesIndexPagination;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class BonusRunController extends Controller
{
    public function index(Request $request): View
    {
        $search = ResolvesIndexPagination::search($request);

        $bonusRuns = BonusRun::query()
            ->with(['payrollPeriod', 'creator'])
            ->withSum('items', 'amount')
            ->withCount('items')
            ->when($search, function ($query) use ($search): void {
                $query->where('name', 'like', '%'.$search.'%');
            })
            ->latest()
            ->paginate(ResolvesIndexPagination::perPage($request))
            ->withQueryString();

        return view('admin.bonus-runs.index', [
            'bonusRuns' => $bonusRuns,
            'periods' => PayrollPeriod::query()
                ->where('status', PayrollPeriodStatus::Draft)
                ->orderByDesc('start_date')
                ->get(),
            'employees' => Employee::query()
                ->with('user')
                ->where('status', EmploymentStatus::Active)
                ->orderBy('employee_number')
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'payroll_period_id' => ['required', 'exists:payroll_periods,id'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.employee_id' => ['required', 'exists:employees,id', 'distinct'],
            'items.*.amount' => ['required', 'numeric', 'min:0.01'],
            'items.*.label' => ['nullable', 'string', 'max:255'],
        ]);

        $period = PayrollPeriod::query()->findOrFail($validated['payroll_period_id']);

        if ($period->status !== PayrollPeriodStatus::Draft) {
            return back()->withInput()->with('error', __('Bonuses can only target a draft payroll period.'));
        }

        $bonusRun = BonusRun::query()->create([
            'name' => $validated['name'],
            'payroll_period_id' => $validated['payroll_period_id'],
            'notes' => $validated['notes'] ?? null,
            'status' => BonusRunStatus::Draft,
            'created_by' => $request->user()->id,
        ]);

        foreach ($validated['items'] as $item) {
            $bonusRun->items()->create([
                'employee_id' => $item['employee_id'],
                'amount' => $item['amount'],
                'label' => $item['label'] ?? null,
            ]);
        }

        return redirect()->route('admin.bonus-runs.index')->with('success', __('Bonus run created as draft.'));
    }

    public function apply(Request $request, BonusRun $bonusRun, ApplyBonusRunAction $action): RedirectResponse
    {
        try {
            $action->handle($bonusRun, $request->user());
        } catch (InvalidArgumentException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', __('Bonus run applied to payroll.'));
    }

    public function destroy(BonusRun $bonusRun): RedirectResponse
    {
        if (! $bonusRun->isDraft()) {
            return back()->with('error', __('Only draft bonus runs can be deleted.'));
        }

        $bonusRun->delete();

        return back()->with('success', __('Bonus run deleted.'));
    }
}
