<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Planning\CancelRecurringOperationAction;
use App\Actions\Planning\PauseRecurringOperationAction;
use App\Actions\Planning\ResumeRecurringOperationAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Planning\CancelRecurringOperationRequest;
use App\Http\Requests\Planning\PauseRecurringOperationRequest;
use App\Http\Requests\Planning\ResumeRecurringOperationRequest;
use App\Models\RecurringOperation;
use Illuminate\Http\RedirectResponse;

class RecurringOperationStatusController extends Controller
{
    public function pause(PauseRecurringOperationRequest $request, RecurringOperation $recurringOperation, PauseRecurringOperationAction $action): RedirectResponse
    {
        $this->authorize('update', $recurringOperation);

        $action->execute($recurringOperation, $request->user());

        return back()->with('success', 'Operacao recorrente pausada.');
    }

    public function resume(ResumeRecurringOperationRequest $request, RecurringOperation $recurringOperation, ResumeRecurringOperationAction $action): RedirectResponse
    {
        $this->authorize('update', $recurringOperation);

        $action->execute($recurringOperation, $request->user());

        return back()->with('success', 'Operacao recorrente retomada.');
    }

    public function cancel(CancelRecurringOperationRequest $request, RecurringOperation $recurringOperation, CancelRecurringOperationAction $action): RedirectResponse
    {
        $this->authorize('update', $recurringOperation);

        $action->execute($recurringOperation, $request->user());

        return back()->with('success', 'Operacao recorrente cancelada.');
    }
}
