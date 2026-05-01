<?php

namespace App\Actions\Planning;

use App\Models\OperationalPlan;
use App\Models\User;
use App\Services\Tickets\ActivityLogger;
use Illuminate\Auth\Access\AuthorizationException;

class ApproveOperationalPlanAction
{
    public function __construct(private readonly ActivityLogger $activityLogger)
    {
    }

    public function execute(OperationalPlan $plan, User $performedBy): OperationalPlan
    {
        if (! $performedBy->can('planning.approve')) {
            throw new AuthorizationException('Sem permissao para aprovar planos.');
        }

        $plan->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => $performedBy->id,
        ]);

        $this->activityLogger->log(
            subject: $plan,
            action: 'planning.operational_plan.approved',
            user: $performedBy,
            organization: $plan->organization,
            newValues: ['status' => 'approved', 'approved_by' => $performedBy->id],
            description: 'Plano operacional aprovado.',
        );

        return $plan->fresh();
    }
}
