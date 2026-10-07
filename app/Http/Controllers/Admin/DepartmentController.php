<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Support\ResolvesIndexPagination;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DepartmentController extends Controller
{
    public function index(Request $request): View
    {
        $search = ResolvesIndexPagination::search($request);

        $departments = Department::query()
            ->withCount('employees')
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

        return view('admin.departments.index', [
            'departments' => $departments,
            'editingDepartment' => $this->resolveEditingDepartment($request),
        ]);
    }

    public function create(): RedirectResponse
    {
        return redirect()->route('admin.departments.index', ['modal' => 'create']);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:20', 'unique:departments,code'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        Department::query()->create([
            ...$validated,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.departments.index')->with('success', 'Department created.');
    }

    public function edit(Department $department): RedirectResponse
    {
        return redirect()->route('admin.departments.index', [
            'modal' => 'edit',
            'id' => $department->id,
        ]);
    }

    private function resolveEditingDepartment(Request $request): ?Department
    {
        $formModal = old('form_modal');

        if (is_string($formModal) && str_starts_with($formModal, 'edit-department-')) {
            return Department::query()->find((int) str_replace('edit-department-', '', $formModal));
        }

        if ($request->query('modal') === 'edit' && $request->filled('id')) {
            return Department::query()->find($request->integer('id'));
        }

        return null;
    }

    public function update(Request $request, Department $department): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:20', Rule::unique('departments', 'code')->ignore($department->id)],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $department->update([
            ...$validated,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.departments.index')->with('success', 'Department updated.');
    }

    public function destroy(Department $department): RedirectResponse
    {
        $department->delete();

        return redirect()->route('admin.departments.index')->with('success', 'Department deleted.');
    }
}
