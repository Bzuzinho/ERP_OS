<?php

namespace App\Actions\Planning;

use App\Models\OperationalPlan;
use App\Models\Task;
use App\Models\User;
use App\Services\Planning\OperationalPlanProgressService;
use App\Services\Tickets\ActivityLogger;

class AttachTaskToOperationalPlanAction
{
    public function __construct(
        private readonly ActivityLogger $activityLogger,
        private readonly OperationalPlanProgressService $progressService,
    ) {
    }

    public function execute(OperationalPlan $plan, Task $task, User $performedBy, array $pivotData = []): void
    {
        $alreadyAttached = $plan->tasks()->whereKey($task->id)->exists();

        if (! $alreadyAttached) {
            $plan->tasks()->attach($task->id, [
                'position' => (int) ($pivotData['position'] ?? 0),
                'is_milestone' => (bool) ($pivotData['is_milestone'] ?? false),
                'weight' => $pivotData['weight'] ?? null,
            ]);
        }

        $progress = $this->progressService->recalculate($plan);

        $this->activityLogger->log(
            subject: $plan,
            action: 'planning.operational_plan.task_attached',
            user: $performedBy,
            organization: $plan->organization,
            newValues: ['task_id' => $task->id, 'progress_percent' => $progress],
            description: $alreadyAttached ? 'Tarefa ja estava associada ao plano.' : 'Tarefa associada ao plano operacional.',
        );
    }
}
