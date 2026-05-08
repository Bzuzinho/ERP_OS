<?php

namespace App\Services\Notifications;

use App\Models\Task;
use App\Models\User;

class TaskNotificationService
{
    public function __construct(
        private readonly NotificationService $notificationService,
    ) {
    }

    public function notifyTaskSubmittedForValidation(Task $task, ?User $actor = null): void
    {
        $users = User::query()
            ->where('organization_id', $task->organization_id)
            ->where('is_active', true)
            ->where(function ($query) {
                $query
                    ->whereHas('permissions', fn ($permissionQuery) => $permissionQuery->where('name', 'tasks.validate'))
                    ->orWhereHas('roles.permissions', fn ($permissionQuery) => $permissionQuery->where('name', 'tasks.validate'));
            })
            ->get()
            ->when($task->assigned_to, fn ($collection) => $collection->push(User::query()->find($task->assigned_to)))
            ->filter()
            ->when($actor, fn ($collection) => $collection->where('id', '!=', $actor->id))
            ->unique('id')
            ->values();

        if ($users->isEmpty()) {
            return;
        }

        $this->notificationService->createForUsers($users, [
            'organization_id' => $task->organization_id,
            'type' => 'task_submitted_for_validation',
            'title' => 'Tarefa pronta para validação',
            'message' => sprintf('A tarefa "%s" foi enviada para validação.', $task->title),
            'notifiable' => $task,
            'action_url' => route('admin.tasks.show', $task, false),
            'priority' => $task->priority ?? 'normal',
            'created_by' => $actor?->id,
            'data' => [
                'task_id' => $task->id,
                'task_status' => $task->status,
            ],
        ]);
    }

    public function notifyTaskValidated(Task $task, ?User $actor = null): void
    {
        if (! $task->assigned_to) {
            return;
        }

        $assignee = User::query()->find($task->assigned_to);

        if (! $assignee || ! $assignee->is_active) {
            return;
        }

        $this->notificationService->createForUsers([$assignee], [
            'organization_id' => $task->organization_id,
            'type' => 'task_validated',
            'title' => 'Tarefa validada',
            'message' => sprintf('A tarefa "%s" foi validada.', $task->title),
            'notifiable' => $task,
            'action_url' => route('admin.tasks.show', $task, false),
            'priority' => $task->priority ?? 'normal',
            'created_by' => $actor?->id,
            'data' => [
                'task_id' => $task->id,
                'task_status' => $task->status,
                'validation_notes' => $task->validation_notes,
            ],
        ]);
    }

    public function notifyTaskReopened(Task $task, ?User $actor = null, ?string $reason = null): void
    {
        if (! $task->assigned_to) {
            return;
        }

        $assignee = User::query()->find($task->assigned_to);

        if (! $assignee || ! $assignee->is_active) {
            return;
        }

        $message = sprintf('A tarefa "%s" foi reaberta.', $task->title);
        if ($reason) {
            $message .= sprintf(' Motivo: %s', $reason);
        }

        $this->notificationService->createForUsers([$assignee], [
            'organization_id' => $task->organization_id,
            'type' => 'task_reopened',
            'title' => 'Tarefa reaberta',
            'message' => $message,
            'notifiable' => $task,
            'action_url' => route('admin.tasks.show', $task, false),
            'priority' => $task->priority ?? 'normal',
            'created_by' => $actor?->id,
            'data' => [
                'task_id' => $task->id,
                'task_status' => $task->status,
                'reason' => $reason,
            ],
        ]);
    }
}
