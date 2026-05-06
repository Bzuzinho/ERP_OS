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
        // Extract checklists from data if present
        $checklists = [];
        if (isset($data['checklists']) && is_string($data['checklists'])) {
            $checklists = json_decode($data['checklists'], true) ?? [];
            unset($data['checklists']);
        }

        $task = Task::create([
            ...$data,
            'organization_id' => $data['organization_id'] ?? $creator->organization_id,
            'created_by' => $creator->id,
        ]);

        // Create checklists and items
        foreach ($checklists as $checklistData) {
            $checklist = $task->checklists()->create([
                'title' => $checklistData['title'],
                'position' => $checklistData['position'] ?? 0,
            ]);

            if (isset($checklistData['items']) && is_array($checklistData['items'])) {
                foreach ($checklistData['items'] as $itemData) {
                    $checklist->items()->create([
                        'label' => $itemData['label'],
                        'position' => $itemData['position'] ?? 0,
                    ]);
                }
            }
        }

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
