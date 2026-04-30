<?php

namespace App\Actions\Tasks;

use App\Models\Task;
use App\Models\User;
use App\Services\Tickets\ActivityLogger;

class CompleteTaskAction
{
    public function __construct(private readonly ActivityLogger $activityLogger)
    {
    }

    public function execute(Task $task, User $performedBy): Task
    {
        $task->status = 'done';
        $task->completed_at = now();
        $task->completed_by = $performedBy->id;
        $task->save();

        $this->activityLogger->log(
            subject: $task,
            action: 'task.completed',
            user: $performedBy,
            organization: $task->organization,
            newValues: ['status' => 'done', 'completed_by' => $performedBy->id],
            description: 'Tarefa concluida.',
        );

        return $task;
    }
}
