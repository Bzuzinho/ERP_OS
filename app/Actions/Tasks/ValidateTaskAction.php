<?php

namespace App\Actions\Tasks;

use App\Models\Task;
use App\Models\User;
use App\Services\Notifications\TaskNotificationService;
use App\Services\Tasks\TaskStateTransitionService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;

class ValidateTaskAction
{
    public function __construct(
        private readonly TaskStateTransitionService $taskStateTransitionService,
        private readonly TaskNotificationService $taskNotificationService,
    ) {
    }

    public function execute(Task $task, User $validator, ?string $validationNotes = null): Task
    {
        if (! $validator->can('validate', $task)) {
            throw new AuthorizationException('Sem permissão para validar tarefa.');
        }

        if (! $task->canValidate()) {
            throw ValidationException::withMessages([
                'task' => 'A tarefa não está pendente de validação.',
            ]);
        }

        $task = $this->taskStateTransitionService->transition(
            task: $task,
            newStatus: 'validated',
            performedBy: $validator,
            logAction: 'task.validated',
            description: 'Tarefa validada.',
            attributes: [
                'validated_at' => now(),
                'validated_by' => $validator->id,
                'validation_notes' => $validationNotes,
            ],
        );

        $this->taskNotificationService->notifyTaskValidated($task, $validator);

        return $task;
    }
}
