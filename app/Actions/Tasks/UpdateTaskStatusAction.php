<?php

namespace App\Actions\Tasks;

use App\Models\Task;
use App\Models\User;
use App\Services\Tasks\TaskStateTransitionService;

class UpdateTaskStatusAction
{
    public function __construct(
        private readonly TaskStateTransitionService $taskStateTransitionService,
    )
    {
    }

    public function execute(Task $task, string $status, User $performedBy, string $logAction = 'task.status_updated'): Task
    {
        return $this->taskStateTransitionService->transition(
            task: $task,
            newStatus: $status,
            performedBy: $performedBy,
            logAction: $logAction,
            description: 'Estado da tarefa atualizado.',
        );
    }
}
