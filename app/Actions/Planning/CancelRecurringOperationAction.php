<?php

namespace App\Actions\Planning;

use App\Models\RecurringOperation;
use App\Models\User;
use App\Services\Tickets\ActivityLogger;

class CancelRecurringOperationAction
{
    public function __construct(private readonly ActivityLogger $activityLogger)
    {
    }

    public function execute(RecurringOperation $operation, User $performedBy): RecurringOperation
    {
        $operation->update(['status' => 'cancelled']);

        $this->activityLogger->log(
            subject: $operation,
            action: 'planning.recurring_operation.cancelled',
            user: $performedBy,
            organization: $operation->organization,
            newValues: ['status' => 'cancelled'],
            description: 'Operacao recorrente cancelada.',
        );

        return $operation->fresh();
    }
}
