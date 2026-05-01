<?php

namespace App\Actions\Planning;

use App\Models\RecurringOperationRun;
use App\Models\User;
use App\Services\Planning\RecurringOperationExecutor;
use App\Services\Tickets\ActivityLogger;
use Illuminate\Support\Facades\DB;

class ExecuteRecurringOperationRunAction
{
    public function __construct(
        private readonly RecurringOperationExecutor $executor,
        private readonly ActivityLogger $activityLogger,
    ) {
    }

    public function execute(RecurringOperationRun $run, User $performedBy): RecurringOperationRun
    {
        return DB::transaction(function () use ($run, $performedBy) {
            $executed = $this->executor->execute($run->loadMissing('recurringOperation'), $performedBy);

            $this->activityLogger->log(
                subject: $executed,
                action: 'planning.recurring_operation.run_executed',
                user: $performedBy,
                organization: $run->recurringOperation->organization,
                newValues: [
                    'status' => $executed->status,
                    'generated_task_id' => $executed->generated_task_id,
                    'generated_event_id' => $executed->generated_event_id,
                ],
                description: 'Execucao de operacao recorrente processada.',
            );

            return $executed;
        });
    }
}
