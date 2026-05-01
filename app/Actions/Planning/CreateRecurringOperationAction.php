<?php

namespace App\Actions\Planning;

use App\Models\RecurringOperation;
use App\Models\User;
use App\Services\Planning\RecurringOperationScheduler;
use App\Services\Tickets\ActivityLogger;

class CreateRecurringOperationAction
{
    public function __construct(
        private readonly RecurringOperationScheduler $scheduler,
        private readonly ActivityLogger $activityLogger,
    ) {
    }

    public function execute(User $creator, array $data): RecurringOperation
    {
        $operation = RecurringOperation::create([
            ...$data,
            'organization_id' => $creator->organization_id,
            'created_by' => $creator->id,
        ]);

        $operation->next_run_at = $this->scheduler->calculateNextRunAt($operation);
        $operation->save();

        $this->activityLogger->log(
            subject: $operation,
            action: 'planning.recurring_operation.created',
            user: $creator,
            organization: $operation->organization,
            newValues: $operation->only(['title', 'operation_type', 'status', 'frequency', 'interval', 'next_run_at']),
            description: 'Operacao recorrente criada.',
        );

        return $operation;
    }
}
