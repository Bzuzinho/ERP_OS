<?php

namespace App\Actions\Tasks;

use App\Models\Task;
use App\Models\User;
use App\Services\Tasks\TaskStateTransitionService;

class CompleteTaskAction
{
    public function __construct(private readonly TaskStateTransitionService $taskStateTransitionService)
    {
    }

    public function execute(Task $task, User $performedBy): Task
    {
        $targetStatus = config('juntaos.tasks.validation_workflow_enabled', true)
            ? 'pending_validation'
            : 'done';

        return $this->taskStateTransitionService->transition(
            task: $task,
            newStatus: $targetStatus,
            performedBy: $performedBy,
            logAction: 'task.completed',
            description: 'Tarefa concluída explicitamente.',
        );
    }
}

