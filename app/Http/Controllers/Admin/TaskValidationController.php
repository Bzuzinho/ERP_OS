<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Tasks\ReopenTaskAction;
use App\Actions\Tasks\SubmitTaskForValidationAction;
use App\Actions\Tasks\ValidateTaskAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tasks\ReopenTaskRequest;
use App\Http\Requests\Tasks\SubmitTaskForValidationRequest;
use App\Http\Requests\Tasks\ValidateTaskRequest;
use App\Models\Task;
use App\Support\OrganizationScope;
use Illuminate\Http\RedirectResponse;

class TaskValidationController extends Controller
{
    public function submitForValidation(
        SubmitTaskForValidationRequest $request,
        Task $task,
        SubmitTaskForValidationAction $submitTaskForValidationAction,
    ): RedirectResponse {
        OrganizationScope::ensureModelBelongsToUserOrganization($task, $request->user());

        $submitTaskForValidationAction->execute($task, $request->user());

        return back()->with('success', 'Tarefa enviada para validação.');
    }

    public function validateTask(
        ValidateTaskRequest $request,
        Task $task,
        ValidateTaskAction $validateTaskAction,
    ): RedirectResponse {
        OrganizationScope::ensureModelBelongsToUserOrganization($task, $request->user());

        $validateTaskAction->execute($task, $request->user(), $request->validated('validation_notes'));

        return back()->with('success', 'Tarefa validada com sucesso.');
    }

    public function reopen(
        ReopenTaskRequest $request,
        Task $task,
        ReopenTaskAction $reopenTaskAction,
    ): RedirectResponse {
        OrganizationScope::ensureModelBelongsToUserOrganization($task, $request->user());

        $reopenTaskAction->execute(
            $task,
            $request->user(),
            $request->validated('reason'),
            $request->validated('target_status'),
        );

        return back()->with('success', 'Tarefa reaberta com sucesso.');
    }
}
