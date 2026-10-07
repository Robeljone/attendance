<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PayComponentCalculation;
use App\Enums\PayslipLineType;
use App\Http\Controllers\Controller;
use App\Models\PayComponent;
use App\Support\ResolvesIndexPagination;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PayComponentController extends Controller
{
    public function index(Request $request): View
    {
        $search = ResolvesIndexPagination::search($request);

        $components = PayComponent::query()
            ->when($search, function ($query) use ($search): void {
                $term = '%'.$search.'%';
                $query->where(function ($inner) use ($term): void {
                    $inner->where('name', 'like', $term)
                        ->orWhere('code', 'like', $term);
                });
            })
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(ResolvesIndexPagination::perPage($request))
            ->withQueryString();

        return view('admin.pay-components.index', [
            'components' => $components,
            'editingComponent' => $this->resolveEditingComponent($request),
            'types' => PayslipLineType::cases(),
            'calculations' => PayComponentCalculation::cases(),
        ]);
    }

    public function create(): RedirectResponse
    {
        return redirect()->route('admin.pay-components.index', ['modal' => 'create']);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validated($request);

        PayComponent::query()->create([
            ...$validated,
            'is_taxable' => $request->boolean('is_taxable', true),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.pay-components.index')->with('success', __('Pay component created.'));
    }

    public function edit(PayComponent $payComponent): RedirectResponse
    {
        return redirect()->route('admin.pay-components.index', [
            'modal' => 'edit',
            'id' => $payComponent->id,
        ]);
    }

    public function update(Request $request, PayComponent $payComponent): RedirectResponse
    {
        $validated = $this->validated($request, $payComponent->id);

        $payComponent->update([
            ...$validated,
            'is_taxable' => $request->boolean('is_taxable'),
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.pay-components.index')->with('success', __('Pay component updated.'));
    }

    public function destroy(PayComponent $payComponent): RedirectResponse
    {
        $payComponent->delete();

        return redirect()->route('admin.pay-components.index')->with('success', __('Pay component deleted.'));
    }

    private function resolveEditingComponent(Request $request): ?PayComponent
    {
        $formModal = old('form_modal');

        if (is_string($formModal) && str_starts_with($formModal, 'edit-pay-component-')) {
            return PayComponent::query()->find((int) str_replace('edit-pay-component-', '', $formModal));
        }

        if ($request->query('modal') === 'edit' && $request->filled('id')) {
            return PayComponent::query()->find($request->integer('id'));
        }

        return null;
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?int $componentId = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'max:50',
                'alpha_dash',
                Rule::unique('pay_components', 'code')->ignore($componentId),
            ],
            'type' => ['required', Rule::enum(PayslipLineType::class)],
            'calculation' => ['required', Rule::enum(PayComponentCalculation::class)],
            'default_amount' => ['required', 'numeric', 'min:0'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'is_taxable' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }
}
