<?php

namespace App\Actions\Planning;

use App\Models\OperationalPlan;
use App\Models\User;
use App\Services\Tickets\ActivityLogger;
use InvalidArgumentException;

class CancelOperationalPlanAction
{
    public function __construct(private readonly ActivityLogger $activityLogger)
    {
    }

    public function execute(OperationalPlan $plan, User $performedBy, string $reason): OperationalPlan
    {
        if (trim($reason) === '') {
            throw new InvalidArgumentException('Motivo de cancelamento obrigatorio.');
        }

        $plan->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancelled_by' => $performedBy->id,
            'cancellation_reason' => $reason,
        ]);

        $this->activityLogger->log(
            subject: $plan,
            action: 'planning.operational_plan.cancelled',
            user: $performedBy,
            organization: $plan->organization,
            newValues: ['status' => 'cancelled', 'cancellation_reason' => $reason],
            description: 'Plano operacional cancelado.',
        );

        return $plan->fresh();
    }
}
