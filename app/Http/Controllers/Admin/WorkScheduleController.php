<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WorkSchedule;
use App\Support\ResolvesIndexPagination;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WorkScheduleController extends Controller
{
    public function index(Request $request): View
    {
        $search = ResolvesIndexPagination::search($request);

        $schedules = WorkSchedule::query()
            ->withCount('employees')
            ->when($search, function ($query) use ($search): void {
                $query->where('name', 'like', '%'.$search.'%');
            })
            ->orderBy('name')
            ->paginate(ResolvesIndexPagination::perPage($request))
            ->withQueryString();

        return view('admin.schedules.index', [
            'schedules' => $schedules,
            'editingSchedule' => $this->resolveEditingSchedule($request),
        ]);
    }

    public function create(): RedirectResponse
    {
        return redirect()->route('admin.schedules.index', ['modal' => 'create']);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'work_days' => ['required', 'array', 'min:1'],
            'work_days.*' => ['integer', 'between:1,7'],
            'break_minutes' => ['nullable', 'integer', 'min:0', 'max:240'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        WorkSchedule::query()->create([
            'name' => $validated['name'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'work_days' => array_map('intval', $validated['work_days']),
            'break_minutes' => $validated['break_minutes'] ?? 60,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.schedules.index')->with('success', 'Schedule created.');
    }

    public function edit(WorkSchedule $schedule): RedirectResponse
    {
        return redirect()->route('admin.schedules.index', [
            'modal' => 'edit',
            'id' => $schedule->id,
        ]);
    }

    private function resolveEditingSchedule(Request $request): ?WorkSchedule
    {
        $formModal = old('form_modal');

        if (is_string($formModal) && str_starts_with($formModal, 'edit-schedule-')) {
            return WorkSchedule::query()->find((int) str_replace('edit-schedule-', '', $formModal));
        }

        if ($request->query('modal') === 'edit' && $request->filled('id')) {
            return WorkSchedule::query()->find($request->integer('id'));
        }

        return null;
    }

    public function update(Request $request, WorkSchedule $schedule): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'start_time' => ['required', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
            'end_time' => ['required', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
            'work_days' => ['required', 'array', 'min:1'],
            'work_days.*' => ['integer', 'between:1,7'],
            'break_minutes' => ['nullable', 'integer', 'min:0', 'max:240'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $schedule->update([
            'name' => $validated['name'],
            'start_time' => substr($validated['start_time'], 0, 5),
            'end_time' => substr($validated['end_time'], 0, 5),
            'work_days' => array_map('intval', $validated['work_days']),
            'break_minutes' => $validated['break_minutes'] ?? 60,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.schedules.index')->with('success', 'Schedule updated.');
    }

    public function destroy(WorkSchedule $schedule): RedirectResponse
    {
        $schedule->delete();

        return redirect()->route('admin.schedules.index')->with('success', 'Schedule deleted.');
    }
}
