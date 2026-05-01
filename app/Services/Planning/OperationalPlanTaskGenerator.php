<?php

namespace App\Services\Planning;

use App\Models\OperationalPlan;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Collection;

class OperationalPlanTaskGenerator
{
    public function generate(OperationalPlan $plan, User $performedBy, array $taskTemplates): Collection
    {
        $createdTasks = collect();

        foreach ($taskTemplates as $index => $template) {
            $task = Task::create([
                'organization_id' => $plan->organization_id ?? $performedBy->organization_id,
                'ticket_id' => $plan->related_ticket_id,
                'assigned_to' => $template['assigned_to'] ?? $plan->owner_user_id,
                'created_by' => $performedBy->id,
                'title' => $template['title'],
                'description' => $template['description'] ?? null,
                'status' => 'pending',
                'priority' => $template['priority'] ?? 'normal',
                'start_date' => $template['start_date'] ?? $plan->start_date,
                'due_date' => $template['due_date'] ?? $plan->end_date,
            ]);

            $plan->tasks()->syncWithoutDetaching([
                $task->id => [
                    'position' => (int) ($template['position'] ?? $index),
                    'is_milestone' => (bool) ($template['is_milestone'] ?? false),
                    'weight' => $template['weight'] ?? null,
                ],
            ]);

            $createdTasks->push($task);
        }

        return $createdTasks;
    }
}
