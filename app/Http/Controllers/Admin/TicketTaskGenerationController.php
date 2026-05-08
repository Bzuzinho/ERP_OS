<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Tickets\GenerateTasksFromTicketAction;
use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\Ticket;
use App\Models\User;
use App\Services\Scopes\UserOperationalScopeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class TicketTaskGenerationController extends Controller
{
    public function store(
        Ticket $ticket,
        GenerateTasksFromTicketAction $generateTasksFromTicketAction,
        UserOperationalScopeService $scopeService,
    ): RedirectResponse {
        $this->authorize('generateTasks', $ticket);

        $scopeService->validateContext(
            request()->user(),
            $ticket->organization_id,
            $ticket->department_id,
            $ticket->service_area_id,
        );

        $data = request()->validate([
            'tasks' => ['required', 'array', 'min:1'],
            'tasks.*.title' => ['required', 'string', 'max:255'],
            'tasks.*.description' => ['nullable', 'string'],
            'tasks.*.assigned_to' => ['nullable', 'integer', 'exists:users,id'],
            'tasks.*.priority' => ['nullable', Rule::in(Task::PRIORITIES)],
            'tasks.*.due_date' => ['nullable', 'date'],
        ]);

        foreach ($data['tasks'] as $index => $taskData) {
            if (! isset($taskData['assigned_to']) || $taskData['assigned_to'] === null) {
                continue;
            }

            $assignee = User::query()->find($taskData['assigned_to']);
            if (! $assignee || (int) $assignee->organization_id !== (int) $ticket->organization_id) {
                throw ValidationException::withMessages([
                    "tasks.$index.assigned_to" => 'Responsável inválido para o contexto do pedido.',
                ]);
            }
        }

        $createdTasks = $generateTasksFromTicketAction->execute(
            ticket: $ticket,
            actor: request()->user(),
            tasks: $data['tasks'],
        );

        return to_route('admin.tickets.show', $ticket)
            ->with('success', sprintf('%d tarefa(s) criada(s) a partir do pedido.', $createdTasks->count()));
    }
}
