<?php

namespace App\Http\Controllers\Admin;

use App\Actions\ApplySalaryStructureAction;
use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\PayComponent;
use App\Models\SalaryStructure;
use App\Support\ResolvesIndexPagination;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SalaryStructureController extends Controller
{
    public function index(Request $request): View
    {
        $search = ResolvesIndexPagination::search($request);

        $structures = SalaryStructure::query()
            ->withCount('employees')
            ->with('payComponents')
            ->when($search, function ($query) use ($search): void {
                $term = '%'.$search.'%';
                $query->where(function ($inner) use ($term): void {
                    $inner->where('name', 'like', $term)
                        ->orWhere('code', 'like', $term);
                });
            })
            ->orderBy('name')
            ->paginate(ResolvesIndexPagination::perPage($request))
            ->withQueryString();

        return view('admin.salary-structures.index', [
            'structures' => $structures,
            'editingStructure' => $this->resolveEditingStructure($request),
            'payComponents' => PayComponent::query()->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(),
            'employees' => Employee::query()->with('user')->orderBy('employee_number')->get(),
        ]);
    }

    public function create(): RedirectResponse
    {
        return redirect()->route('admin.salary-structures.index', ['modal' => 'create']);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validated($request);

        $structure = SalaryStructure::query()->create([
            'name' => $validated['name'],
            'code' => $validated['code'],
            'description' => $validated['description'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        $this->syncComponents($structure, $validated);

        return redirect()->route('admin.salary-structures.index')->with('success', __('Salary structure created.'));
    }

    public function update(Request $request, SalaryStructure $salaryStructure): RedirectResponse
    {
        $validated = $this->validated($request, $salaryStructure->id);

        $salaryStructure->update([
            'name' => $validated['name'],
            'code' => $validated['code'],
            'description' => $validated['description'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        $this->syncComponents($salaryStructure, $validated);

        return redirect()->route('admin.salary-structures.index')->with('success', __('Salary structure updated.'));
    }

    public function destroy(SalaryStructure $salaryStructure): RedirectResponse
    {
        $salaryStructure->delete();

        return redirect()->route('admin.salary-structures.index')->with('success', __('Salary structure deleted.'));
    }

    public function apply(Request $request, SalaryStructure $salaryStructure, ApplySalaryStructureAction $action): RedirectResponse
    {
        $validated = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
        ]);

        $employee = Employee::query()->findOrFail($validated['employee_id']);
        $action->handle($employee, $salaryStructure);

        return back()->with('success', __('Salary structure applied to employee.'));
    }

    private function resolveEditingStructure(Request $request): ?SalaryStructure
    {
        $formModal = old('form_modal');

        if (is_string($formModal) && str_starts_with($formModal, 'edit-salary-structure-')) {
            return SalaryStructure::query()
                ->with('payComponents')
                ->find((int) str_replace('edit-salary-structure-', '', $formModal));
        }

        if ($request->query('modal') === 'edit' && $request->filled('id')) {
            return SalaryStructure::query()->with('payComponents')->find($request->integer('id'));
        }

        return null;
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?int $structureId = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'max:50',
                'alpha_dash',
                Rule::unique('salary_structures', 'code')->ignore($structureId),
            ],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
            'components' => ['nullable', 'array'],
            'components.*.enabled' => ['nullable', 'boolean'],
        ]);
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function syncComponents(SalaryStructure $structure, array $validated): void
    {
        $inputs = $validated['components'] ?? [];
        $defaults = PayComponent::query()
            ->whereIn('id', array_keys($inputs))
            ->get()
            ->keyBy('id');
        $sync = [];

        foreach ($inputs as $componentId => $data) {
            $enabled = (bool) ($data['enabled'] ?? false);

            if (! $enabled) {
                continue;
            }

            $component = $defaults->get((int) $componentId);

            if (! $component) {
                continue;
            }

            $sync[(int) $componentId] = [
                'amount' => (float) $component->default_amount,
                'is_enabled' => true,
            ];
        }

        $structure->payComponents()->sync($sync);
    }
}
