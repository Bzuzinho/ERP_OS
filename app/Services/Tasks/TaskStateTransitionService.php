<?php

namespace App\Services\Tasks;

use App\Models\Task;
use App\Models\User;
use App\Services\Tickets\ActivityLogger;
use App\Services\Tickets\TicketResolutionService;

class TaskStateTransitionService
{
    public function __construct(
        private readonly ActivityLogger $activityLogger,
        private readonly TicketResolutionService $ticketResolutionService,
    ) {
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function transition(
        Task $task,
        string $newStatus,
        User $performedBy,
        string $logAction = 'task.status_updated',
        ?string $description = null,
        array $attributes = [],
        bool $incrementReopenCount = false,
    ): Task {
        $oldStatus = $task->status;

        $task->status = $newStatus;

        if (in_array($newStatus, ['done', 'pending_validation', 'validated'], true)) {
            $task->completed_at ??= now();
            $task->completed_by ??= $performedBy->id;
        } else {
            $task->completed_at = null;
            $task->completed_by = null;
        }

        if ($newStatus === 'validated') {
            $task->validated_at = $attributes['validated_at'] ?? now();
            $task->validated_by = $attributes['validated_by'] ?? $performedBy->id;
            $task->validation_notes = $attributes['validation_notes'] ?? $task->validation_notes;
        } elseif ($newStatus !== 'validated') {
            $task->validated_at = null;
            $task->validated_by = null;
            $task->validation_notes = null;
        }

        if ($incrementReopenCount) {
            $task->reopen_count = (int) $task->reopen_count + 1;
        }

        if (array_key_exists('observations', $attributes)) {
            $task->observations = $attributes['observations'];
        }

        $task->save();

        $this->activityLogger->log(
            subject: $task,
            action: $logAction,
            user: $performedBy,
            organization: $task->organization,
            oldValues: ['status' => $oldStatus],
            newValues: ['status' => $newStatus],
            description: $description ?? 'Estado da tarefa atualizado.',
        );

        if ($task->ticket_id) {
            $this->ticketResolutionService->syncFromTasks(
                $task->ticket()->firstOrFail(),
                $performedBy,
                'Pedido sincronizado após atualização do estado da tarefa.',
            );
        }

        return $task;
    }
}
