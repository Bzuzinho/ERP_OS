<?php

namespace App\Services\Planning;

use App\Models\Event;
use App\Models\RecurringOperationRun;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Throwable;

class RecurringOperationExecutor
{
    public function __construct(private readonly RecurringOperationScheduler $scheduler)
    {
    }

    public function execute(RecurringOperationRun $run, User $performedBy): RecurringOperationRun
    {
        $operation = $run->recurringOperation;

        return DB::transaction(function () use ($run, $operation, $performedBy) {
            try {
                $generatedTask = null;
                $generatedEvent = null;

                if (in_array($operation->operation_type, ['task', 'maintenance', 'cleaning', 'inspection'], true)) {
                    $template = $operation->task_template ?? [];

                    $generatedTask = Task::create([
                        'organization_id' => $operation->organization_id ?? $performedBy->organization_id,
                        'ticket_id' => null,
                        'assigned_to' => $template['assigned_to'] ?? $operation->owner_user_id,
                        'created_by' => $performedBy->id,
                        'title' => $template['title'] ?? $operation->title,
                        'description' => $template['description'] ?? $operation->description,
                        'status' => 'pending',
                        'priority' => $template['priority'] ?? 'normal',
                        'start_date' => $template['start_date'] ?? now()->toDateString(),
                        'due_date' => $template['due_date'] ?? null,
                    ]);
                }

                if ($operation->operation_type === 'event') {
                    $template = $operation->event_template ?? [];
                    $startAt = ! empty($template['start_at']) ? $template['start_at'] : now()->toDateTimeString();
                    $endAt = ! empty($template['end_at']) ? $template['end_at'] : now()->addHour()->toDateTimeString();

                    $generatedEvent = Event::create([
                        'organization_id' => $operation->organization_id ?? $performedBy->organization_id,
                        'title' => $template['title'] ?? $operation->title,
                        'description' => $template['description'] ?? $operation->description,
                        'event_type' => $template['event_type'] ?? 'activity',
                        'status' => $template['status'] ?? 'scheduled',
                        'start_at' => $startAt,
                        'end_at' => $endAt,
                        'location_text' => $template['location_text'] ?? null,
                        'created_by' => $performedBy->id,
                        'related_ticket_id' => null,
                        'related_contact_id' => null,
                        'visibility' => $template['visibility'] ?? 'internal',
                    ]);
                }

                $run->forceFill([
                    'status' => 'executed',
                    'generated_task_id' => $generatedTask?->id,
                    'generated_event_id' => $generatedEvent?->id,
                    'executed_by' => $performedBy->id,
                    'executed_at' => now(),
                    'error_message' => null,
                ])->save();
            } catch (Throwable $exception) {
                $run->forceFill([
                    'status' => 'failed',
                    'executed_by' => $performedBy->id,
                    'executed_at' => now(),
                    'error_message' => $exception->getMessage(),
                ])->save();

                throw $exception;
            }

            $operation->forceFill([
                'last_run_at' => $run->run_at,
                'next_run_at' => $this->scheduler->calculateNextRunAt($operation, $run->run_at),
            ])->save();

            return $run->fresh(['generatedTask', 'generatedEvent']);
        });
    }
}
