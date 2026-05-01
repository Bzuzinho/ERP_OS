<?php

namespace App\Actions\Planning;

use App\Models\OperationalPlan;
use App\Models\Task;
use App\Models\User;
use App\Services\Planning\OperationalPlanProgressService;
use App\Services\Tickets\ActivityLogger;

class DetachTaskFromOperationalPlanAction
{
    public function __construct(
        private readonly ActivityLogger $activityLogger,
        private readonly OperationalPlanProgressService $progressService,
    ) {
    }

    public function execute(OperationalPlan $plan, Task $task, User $performedBy): void
    {
        $plan->tasks()->detach($task->id);

        $progress = $this->progressService->recalculate($plan);

        $this->activityLogger->log(
            subject: $plan,
            action: 'planning.operational_plan.task_detached',
            user: $performedBy,
            organization: $plan->organization,
            newValues: ['task_id' => $task->id, 'progress_percent' => $progress],
            description: 'Tarefa removida do plano operacional.',
        );
    }
}
