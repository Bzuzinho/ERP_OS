<?php

namespace App\Actions\Planning;

use App\Models\RecurringOperation;
use App\Models\User;
use App\Services\Tickets\ActivityLogger;

class PauseRecurringOperationAction
{
    public function __construct(private readonly ActivityLogger $activityLogger)
    {
    }

    public function execute(RecurringOperation $operation, User $performedBy): RecurringOperation
    {
        $operation->update(['status' => 'paused']);

        $this->activityLogger->log(
            subject: $operation,
            action: 'planning.recurring_operation.paused',
            user: $performedBy,
            organization: $operation->organization,
            newValues: ['status' => 'paused'],
            description: 'Operacao recorrente pausada.',
        );

        return $operation->fresh();
    }
}
