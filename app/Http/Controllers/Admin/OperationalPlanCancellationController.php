<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Planning\CancelOperationalPlanAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Planning\CancelOperationalPlanRequest;
use App\Models\OperationalPlan;
use Illuminate\Http\RedirectResponse;

class OperationalPlanCancellationController extends Controller
{
    public function __invoke(CancelOperationalPlanRequest $request, OperationalPlan $operationalPlan, CancelOperationalPlanAction $action): RedirectResponse
    {
        $this->authorize('update', $operationalPlan);

        $action->execute($operationalPlan, $request->user(), $request->validated('cancellation_reason'));

        return back()->with('success', 'Plano cancelado com sucesso.');
    }
}
