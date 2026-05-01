<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Planning\AttachTaskToOperationalPlanAction;
use App\Actions\Planning\DetachTaskFromOperationalPlanAction;
use App\Actions\Planning\GenerateTasksFromOperationalPlanAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Planning\AttachTaskToOperationalPlanRequest;
use App\Http\Requests\Planning\GenerateTasksFromOperationalPlanRequest;
use App\Models\OperationalPlan;
use App\Models\Task;
use Illuminate\Http\RedirectResponse;

class OperationalPlanTaskController extends Controller
{
    public function store(AttachTaskToOperationalPlanRequest $request, OperationalPlan $operationalPlan, AttachTaskToOperationalPlanAction $action): RedirectResponse
    {
        $this->authorize('update', $operationalPlan);

        $task = Task::query()->findOrFail($request->validated('task_id'));

        $action->execute($operationalPlan, $task, $request->user(), $request->validated());

        return back()->with('success', 'Tarefa associada ao plano com sucesso.');
    }

    public function destroy(OperationalPlan $operationalPlan, Task $task, DetachTaskFromOperationalPlanAction $action): RedirectResponse
    {
        $this->authorize('update', $operationalPlan);

        $action->execute($operationalPlan, $task, request()->user());

        return back()->with('success', 'Tarefa removida do plano com sucesso.');
    }

    public function generate(GenerateTasksFromOperationalPlanRequest $request, OperationalPlan $operationalPlan, GenerateTasksFromOperationalPlanAction $action): RedirectResponse
    {
        $this->authorize('update', $operationalPlan);

        $action->execute($operationalPlan, $request->user(), $request->validated('tasks'));

        return back()->with('success', 'Tarefas geradas com sucesso.');
    }
}
