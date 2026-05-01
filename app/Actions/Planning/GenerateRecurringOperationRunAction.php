<?php

namespace App\Actions\Planning;

use App\Models\RecurringOperation;
use App\Models\RecurringOperationRun;
use App\Models\User;
use App\Services\Tickets\ActivityLogger;

class GenerateRecurringOperationRunAction
{
    public function __construct(private readonly ActivityLogger $activityLogger)
    {
    }

    public function execute(RecurringOperation $operation, User $performedBy): ?RecurringOperationRun
    {
        if ($operation->status !== 'active') {
            return null;
        }

        $run = $operation->runs()->create([
            'run_at' => $operation->next_run_at ?? now(),
            'status' => 'pending',
        ]);

        $this->activityLogger->log(
            subject: $run,
            action: 'planning.recurring_operation.run_generated',
            user: $performedBy,
            organization: $operation->organization,
            newValues: ['run_id' => $run->id, 'run_at' => $run->run_at],
            description: 'Execucao pendente gerada para operacao recorrente.',
        );

        return $run;
    }
}
