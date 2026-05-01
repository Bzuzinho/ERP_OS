<?php

namespace App\Actions\Planning;

use App\Models\OperationalPlan;
use App\Models\User;
use App\Services\Planning\OperationalPlanProgressService;
use App\Services\Planning\OperationalPlanTaskGenerator;
use App\Services\Tickets\ActivityLogger;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GenerateTasksFromOperationalPlanAction
{
    public function __construct(
        private readonly OperationalPlanTaskGenerator $taskGenerator,
        private readonly OperationalPlanProgressService $progressService,
        private readonly ActivityLogger $activityLogger,
    ) {
    }

    public function execute(OperationalPlan $plan, User $performedBy, array $taskTemplates): Collection
    {
        return DB::transaction(function () use ($plan, $performedBy, $taskTemplates) {
            $tasks = $this->taskGenerator->generate($plan, $performedBy, $taskTemplates);
            $progress = $this->progressService->recalculate($plan);

            $this->activityLogger->log(
                subject: $plan,
                action: 'planning.operational_plan.tasks_generated',
                user: $performedBy,
                organization: $plan->organization,
                newValues: ['generated_tasks' => $tasks->pluck('id')->all(), 'progress_percent' => $progress],
                description: 'Tarefas geradas automaticamente a partir do plano operacional.',
            );

            return $tasks;
        });
    }
}
