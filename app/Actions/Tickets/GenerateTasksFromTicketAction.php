<?php

namespace App\Actions\Tickets;

use App\Actions\Tasks\CreateTaskAction;
use App\Models\Task;
use App\Models\Ticket;
use App\Models\User;
use App\Services\Tickets\TicketStateTransitionService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GenerateTasksFromTicketAction
{
    public function __construct(
        private readonly CreateTaskAction $createTaskAction,
        private readonly TicketStateTransitionService $stateTransitionService,
    ) {
    }

    public function execute(Ticket $ticket, User $actor, array $tasks): Collection
    {
        if (! $ticket->canGenerateTasks()) {
            throw ValidationException::withMessages([
                'ticket' => 'O pedido não permite geração de tarefas no estado atual.',
            ]);
        }

        return DB::transaction(function () use ($ticket, $actor, $tasks) {
            $createdTasks = collect();

            foreach ($tasks as $taskData) {
                $createdTasks->push($this->createTaskAction->execute($actor, [
                    'organization_id' => $ticket->organization_id,
                    'ticket_id' => $ticket->id,
                    'assigned_to' => $taskData['assigned_to'] ?? null,
                    'title' => $taskData['title'],
                    'description' => $taskData['description'] ?? null,
                    'priority' => $taskData['priority'] ?? Task::PRIORITIES[1],
                    'status' => Task::STATUSES[0],
                    'start_date' => null,
                    'due_date' => $taskData['due_date'] ?? null,
                ]));
            }

            if ($createdTasks->isNotEmpty() && $ticket->status !== 'com_tarefas') {
                $this->stateTransitionService->transition(
                    ticket: $ticket,
                    newStatus: 'com_tarefas',
                    changedBy: $actor,
                    notes: 'Tarefas geradas a partir do pedido.',
                );
            }

            return $createdTasks;
        });
    }
}
