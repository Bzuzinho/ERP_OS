<?php

namespace App\Actions\Planning;

use App\Models\OperationalPlan;
use App\Models\User;
use App\Services\Tickets\ActivityLogger;

class UpdateOperationalPlanStatusAction
{
    public function __construct(private readonly ActivityLogger $activityLogger)
    {
    }

    public function execute(OperationalPlan $plan, User $performedBy, string $status, ?string $cancellationReason = null): OperationalPlan
    {
        $oldStatus = $plan->status;
        $updates = ['status' => $status];

        if ($status === 'completed') {
            $updates['completed_at'] = now();
            $updates['completed_by'] = $performedBy->id;
            $updates['progress_percent'] = 100;
        }

        if ($status === 'cancelled') {
            $updates['cancelled_at'] = now();
            $updates['cancelled_by'] = $performedBy->id;
            $updates['cancellation_reason'] = $cancellationReason;
        }

        $plan->update($updates);

        $this->activityLogger->log(
            subject: $plan,
            action: 'planning.operational_plan.status_updated',
            user: $performedBy,
            organization: $plan->organization,
            oldValues: ['status' => $oldStatus],
            newValues: ['status' => $status],
            description: 'Estado do plano operacional atualizado.',
        );

        return $plan->fresh();
    }
}
