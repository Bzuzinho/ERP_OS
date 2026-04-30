<?php

namespace App\Actions\Tasks;

use App\Models\Task;
use App\Models\User;
use App\Services\Tickets\ActivityLogger;

class UpdateTaskStatusAction
{
    public function __construct(private readonly ActivityLogger $activityLogger)
    {
    }

    public function execute(Task $task, string $status, User $performedBy): Task
    {
        $oldStatus = $task->status;

        $task->status = $status;

        if ($status === 'done') {
            $task->completed_at = now();
            $task->completed_by = $performedBy->id;
        } else {
            $task->completed_at = null;
            $task->completed_by = null;
        }

        $task->save();

        $this->activityLogger->log(
            subject: $task,
            action: 'task.status_updated',
            user: $performedBy,
            organization: $task->organization,
            oldValues: ['status' => $oldStatus],
            newValues: ['status' => $status],
            description: 'Estado da tarefa atualizado.',
        );

        return $task;
    }
}
