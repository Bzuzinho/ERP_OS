<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Planning\ApproveOperationalPlanAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Planning\ApproveOperationalPlanRequest;
use App\Models\OperationalPlan;
use Illuminate\Http\RedirectResponse;

class OperationalPlanApprovalController extends Controller
{
    public function __invoke(ApproveOperationalPlanRequest $request, OperationalPlan $operationalPlan, ApproveOperationalPlanAction $action): RedirectResponse
    {
        $this->authorize('update', $operationalPlan);

        $action->execute($operationalPlan, $request->user());

        return back()->with('success', 'Plano aprovado com sucesso.');
    }
}
