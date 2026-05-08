<?php

namespace App\Actions\Tasks;

use App\Models\Task;
use App\Models\User;
use App\Services\Notifications\TaskNotificationService;
use App\Services\Tasks\TaskStateTransitionService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;

class SubmitTaskForValidationAction
{
    public function __construct(
        private readonly TaskStateTransitionService $taskStateTransitionService,
        private readonly TaskNotificationService $taskNotificationService,
    ) {
    }

    public function execute(Task $task, User $actor): Task
    {
        if (! $actor->can('submitValidation', $task)) {
            throw new AuthorizationException('Sem permissão para enviar tarefa para validação.');
        }

        if (! $task->canSubmitForValidation()) {
            throw ValidationException::withMessages([
                'task' => 'A tarefa não pode ser enviada para validação no estado atual.',
            ]);
        }

        $task = $this->taskStateTransitionService->transition(
            task: $task,
            newStatus: 'pending_validation',
            performedBy: $actor,
            logAction: 'task.submitted_for_validation',
            description: 'Tarefa enviada para validação.',
        );

        $this->taskNotificationService->notifyTaskSubmittedForValidation($task, $actor);

        return $task;
    }
}
