<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Planning\ExecuteRecurringOperationRunAction;
use App\Actions\Planning\GenerateRecurringOperationRunAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Planning\ExecuteRecurringOperationRunRequest;
use App\Models\RecurringOperation;
use App\Models\RecurringOperationRun;
use Illuminate\Http\RedirectResponse;

class RecurringOperationRunController extends Controller
{
    public function store(RecurringOperation $recurringOperation, GenerateRecurringOperationRunAction $action): RedirectResponse
    {
        $this->authorize('update', $recurringOperation);

        $action->execute($recurringOperation, request()->user());

        return back()->with('success', 'Execucao pendente gerada com sucesso.');
    }

    public function execute(ExecuteRecurringOperationRunRequest $request, RecurringOperation $recurringOperation, RecurringOperationRun $run, ExecuteRecurringOperationRunAction $action): RedirectResponse
    {
        $this->authorize('update', $recurringOperation);

        if ((int) $run->recurring_operation_id !== (int) $recurringOperation->id) {
            abort(404);
        }

        $action->execute($run, $request->user());

        return back()->with('success', 'Execucao processada com sucesso.');
    }
}
