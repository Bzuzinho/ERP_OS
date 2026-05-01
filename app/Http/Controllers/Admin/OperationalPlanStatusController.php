<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Planning\UpdateOperationalPlanStatusAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Planning\UpdateOperationalPlanStatusRequest;
use App\Models\OperationalPlan;
use Illuminate\Http\RedirectResponse;

class OperationalPlanStatusController extends Controller
{
    public function update(UpdateOperationalPlanStatusRequest $request, OperationalPlan $operationalPlan, UpdateOperationalPlanStatusAction $action): RedirectResponse
    {
        $this->authorize('update', $operationalPlan);

        $action->execute(
            $operationalPlan,
            $request->user(),
            $request->validated('status'),
            $request->validated('cancellation_reason'),
        );

        return back()->with('success', 'Estado do plano atualizado com sucesso.');
    }
}
