<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OperationalPlan;
use App\Models\OperationalPlanResource;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OperationalPlanResourceController extends Controller
{
    public function store(Request $request, OperationalPlan $operationalPlan): RedirectResponse
    {
        $this->authorize('update', $operationalPlan);

        $data = $request->validate([
            'inventory_item_id' => ['nullable', 'exists:inventory_items,id'],
            'space_id' => ['nullable', 'exists:spaces,id'],
            'quantity' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $operationalPlan->resources()->create($data);

        return back()->with('success', 'Recurso associado ao plano.');
    }

    public function destroy(OperationalPlan $operationalPlan, OperationalPlanResource $resource): RedirectResponse
    {
        $this->authorize('update', $operationalPlan);

        if ((int) $resource->operational_plan_id === (int) $operationalPlan->id) {
            $resource->delete();
        }

        return back()->with('success', 'Recurso removido do plano.');
    }
}
