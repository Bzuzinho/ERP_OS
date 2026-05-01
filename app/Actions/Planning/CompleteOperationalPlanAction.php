<?php

namespace App\Actions\Planning;

use App\Models\OperationalPlan;
use App\Models\User;
use App\Services\Tickets\ActivityLogger;

class CompleteOperationalPlanAction
{
    public function __construct(private readonly ActivityLogger $activityLogger)
    {
    }

    public function execute(OperationalPlan $plan, User $performedBy): OperationalPlan
    {
        $plan->update([
            'status' => 'completed',
            'progress_percent' => 100,
            'completed_at' => now(),
            'completed_by' => $performedBy->id,
        ]);

        $this->activityLogger->log(
            subject: $plan,
            action: 'planning.operational_plan.completed',
            user: $performedBy,
            organization: $plan->organization,
            newValues: ['status' => 'completed', 'progress_percent' => 100],
            description: 'Plano operacional concluido.',
        );

        return $plan->fresh();
    }
}
