<?php

namespace App\Actions\Tasks;

use App\Models\Task;
use App\Models\User;
use App\Services\Notifications\TaskNotificationService;
use App\Services\Tasks\TaskStateTransitionService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;

class ReopenTaskAction
{
    public function __construct(
        private readonly TaskStateTransitionService $taskStateTransitionService,
        private readonly TaskNotificationService $taskNotificationService,
    ) {
    }

    public function execute(Task $task, User $actor, ?string $reason = null, ?string $targetStatus = null): Task
    {
        if (! $actor->can('reopen', $task)) {
            throw new AuthorizationException('Sem permissão para reabrir tarefa.');
        }

        if (! $task->canReopen()) {
            throw ValidationException::withMessages([
                'task' => 'A tarefa não pode ser reaberta no estado atual.',
            ]);
        }

        $status = in_array($targetStatus, ['reopened', 'in_progress'], true)
            ? $targetStatus
            : 'reopened';

        $task = $this->taskStateTransitionService->transition(
            task: $task,
            newStatus: $status,
            performedBy: $actor,
            logAction: 'task.reopened',
            description: 'Tarefa reaberta para execução operacional.',
            attributes: [
                'observations' => $reason,
            ],
            incrementReopenCount: true,
        );

        $this->taskNotificationService->notifyTaskReopened($task, $actor, $reason);

        return $task;
    }
}
