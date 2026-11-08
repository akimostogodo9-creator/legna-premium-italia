<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SaleUnit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

// Étape 2 §3.3 : unités modifiables ; suppression refusée si utilisée
class SaleUnitController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/units/index', [
            'units' => SaleUnit::query()
                ->ordered()
                ->withCount('products')
                ->get(['id', 'name', 'sort_order'])
                ->map(fn (SaleUnit $unit) => [
                    'id' => $unit->id,
                    'name' => $unit->name,
                    'products_count' => $unit->products_count,
                ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:50', 'unique:sale_units,name'],
        ]);

        SaleUnit::create([...$data, 'sort_order' => (int) SaleUnit::max('sort_order') + 1]);

        return back()->with('success', __('admin.messages.saved'));
    }

    public function update(Request $request, SaleUnit $unit): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:50', Rule::unique('sale_units', 'name')->ignore($unit->id)],
        ]);

        $unit->update($data);

        return back()->with('success', __('admin.messages.saved'));
    }

    public function destroy(SaleUnit $unit): RedirectResponse
    {
        $count = $unit->products()->count();

        if ($count > 0) {
            throw ValidationException::withMessages(['unit' => __('admin.units.in_use', ['count' => $count])]);
        }

        $unit->delete();

        return back()->with('success', __('admin.messages.deleted'));
    }
}
