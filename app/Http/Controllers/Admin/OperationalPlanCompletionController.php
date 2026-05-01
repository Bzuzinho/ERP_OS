<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Planning\CompleteOperationalPlanAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Planning\CompleteOperationalPlanRequest;
use App\Models\OperationalPlan;
use Illuminate\Http\RedirectResponse;

class OperationalPlanCompletionController extends Controller
{
    public function __invoke(CompleteOperationalPlanRequest $request, OperationalPlan $operationalPlan, CompleteOperationalPlanAction $action): RedirectResponse
    {
        $this->authorize('update', $operationalPlan);

        $action->execute($operationalPlan, $request->user());

        return back()->with('success', 'Plano concluido com sucesso.');
    }
}
