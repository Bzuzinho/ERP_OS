<?php

namespace App\Actions\Planning;

use App\Models\RecurringOperation;
use App\Models\User;
use App\Services\Planning\RecurringOperationScheduler;
use App\Services\Tickets\ActivityLogger;

class ResumeRecurringOperationAction
{
    public function __construct(
        private readonly RecurringOperationScheduler $scheduler,
        private readonly ActivityLogger $activityLogger,
    ) {
    }

    public function execute(RecurringOperation $operation, User $performedBy): RecurringOperation
    {
        $operation->status = 'active';
        $operation->next_run_at = $this->scheduler->calculateNextRunAt($operation, now());
        $operation->save();

        $this->activityLogger->log(
            subject: $operation,
            action: 'planning.recurring_operation.resumed',
            user: $performedBy,
            organization: $operation->organization,
            newValues: ['status' => 'active', 'next_run_at' => $operation->next_run_at],
            description: 'Operacao recorrente retomada.',
        );

        return $operation->fresh();
    }
}
