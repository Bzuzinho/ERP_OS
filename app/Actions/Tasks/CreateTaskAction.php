<?php

namespace App\Actions\Tasks;

use App\Models\Task;
use App\Models\User;
use App\Services\Tickets\ActivityLogger;

class CreateTaskAction
{
    public function __construct(private readonly ActivityLogger $activityLogger)
    {
    }

    public function execute(User $creator, array $data): Task
    {
        $task = Task::create([
            ...$data,
            'organization_id' => $data['organization_id'] ?? $creator->organization_id,
            'created_by' => $creator->id,
        ]);

        $this->activityLogger->log(
            subject: $task,
            action: 'task.created',
            user: $creator,
            organization: $task->organization,
            newValues: $task->only(['title', 'status', 'priority', 'ticket_id', 'assigned_to', 'due_date']),
            description: 'Tarefa interna criada.',
        );

        return $task;
    }
}
