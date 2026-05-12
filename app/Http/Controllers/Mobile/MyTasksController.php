<?php

namespace App\Http\Controllers\Mobile;

use App\Actions\Tasks\CompleteTaskAction;
use App\Actions\Tasks\ReopenTaskAction;
use App\Actions\Tasks\SubmitTaskForValidationAction;
use App\Actions\Tasks\ValidateTaskAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tasks\ReopenTaskRequest;
use App\Http\Requests\Tasks\SubmitTaskForValidationRequest;
use App\Http\Requests\Tasks\ValidateTaskRequest;
use App\Models\Task;
use App\Models\TaskChecklist;
use App\Support\OrganizationScope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MyTasksController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Task::class);

        $user = $request->user();
        $status = $request->string('status')->toString();

        $query = Task::query()
            ->where('organization_id', $user->organization_id)
            ->where('assigned_to', $user->id)
            ->with(['ticket:id,reference,title', 'assignee:id,name', 'checklists']);

        // Apply status filter
        if ($status) {
            match ($status) {
                'today' => $query->whereDate('due_date', now()->toDateString()),
                'overdue' => $query->whereDate('due_date', '<', now()->toDateString())
                    ->whereNotIn('status', ['done', 'cancelled']),
                'pending' => $query->where('status', 'pending'),
                'in_progress' => $query->where('status', 'in_progress'),
                'reopened' => $query->where('status', 'reopened'),
                'pending_validation' => $query->where('status', 'pending_validation'),
                'done' => $query->where('status', 'done'),
                default => null,
            };
        }

        $tasks = $query
            ->orderBy('priority', 'desc')
            ->orderBy('due_date')
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Mobile/Tasks/Index', [
            'tasks' => $tasks,
            'filters' => compact('status'),
            'statuses' => Task::STATUSES,
        ]);
    }

    public function show(Task $task, Request $request): Response
    {
        $this->authorize('view', $task);
        OrganizationScope::ensureModelBelongsToUserOrganization($task, $request->user());

        $task->load([
            'ticket:id,reference,title',
            'assignee:id,name',
            'creator:id,name',
            'validator:id,name',
            'checklists.items',
            'comments.user:id,name',
            'attachments.uploader:id,name',
            'spaceReservation:id,purpose,notes',
        ]);

        return Inertia::render('Mobile/Tasks/Show', [
            'task' => $task,
            'can' => [
                'start' => $request->user()->can('complete', $task),
                'complete' => $request->user()->can('complete', $task),
                'submitValidation' => $request->user()->can('submitValidation', $task),
                'validate' => $request->user()->can('validate', $task),
                'reopen' => $request->user()->can('reopen', $task),
            ],
        ]);
    }

    public function start(Request $request, Task $task): RedirectResponse
    {
        $this->authorize('complete', $task);
        OrganizationScope::ensureModelBelongsToUserOrganization($task, $request->user());

        if ($task->status === 'pending') {
            $task->update([
                'status' => 'in_progress',
                'start_date' => now()->toDateString(),
            ]);
        }

        return back()->with('success', 'Tarefa iniciada com sucesso.');
    }

    public function complete(
        Request $request,
        Task $task,
        CompleteTaskAction $completeTaskAction
    ): RedirectResponse {
        $this->authorize('complete', $task);
        OrganizationScope::ensureModelBelongsToUserOrganization($task, $request->user());

        $completeTaskAction->execute($task, $request->user());

        return back()->with('success', 'Tarefa concluída com sucesso.');
    }

    public function submitForValidation(
        SubmitTaskForValidationRequest $request,
        Task $task,
        SubmitTaskForValidationAction $submitTaskForValidationAction
    ): RedirectResponse {
        $this->authorize('submitValidation', $task);
        OrganizationScope::ensureModelBelongsToUserOrganization($task, $request->user());

        $submitTaskForValidationAction->execute($task, $request->user());

        return back()->with('success', 'Tarefa enviada para validação com sucesso.');
    }

    public function reopen(
        ReopenTaskRequest $request,
        Task $task,
        ReopenTaskAction $reopenTaskAction
    ): RedirectResponse {
        $this->authorize('reopen', $task);
        OrganizationScope::ensureModelBelongsToUserOrganization($task, $request->user());

        $reopenTaskAction->execute(
            $task,
            $request->user(),
            $request->validated('reason'),
            $request->validated('target_status') ?? 'pending'
        );

        return back()->with('success', 'Tarefa reaberta com sucesso.');
    }

    public function updateChecklistItem(
        Request $request,
        Task $task,
        TaskChecklist $checklist
    ): RedirectResponse {
        $this->authorize('complete', $task);
        OrganizationScope::ensureModelBelongsToUserOrganization($task, $request->user());

        $request->validate([
            'is_completed' => 'required|boolean',
        ]);

        $checklist->update([
            'is_completed' => $request->boolean('is_completed'),
            'completed_by' => $request->boolean('is_completed') ? $request->user()->id : null,
            'completed_at' => $request->boolean('is_completed') ? now() : null,
        ]);

        return back()->with('success', 'Checklist atualizado com sucesso.');
    }

    public function addObservation(
        Request $request,
        Task $task
    ): RedirectResponse {
        $this->authorize('complete', $task);
        OrganizationScope::ensureModelBelongsToUserOrganization($task, $request->user());

        $request->validate([
            'observation' => 'required|string|max:1000',
        ]);

        $observations = $task->observations ?? '';
        $timestamp = now()->format('d/m/Y H:i');
        $userName = $request->user()->name;

        $newObservation = "[{$timestamp}] {$userName}: {$request->input('observation')}\n";
        $task->update([
            'observations' => $observations . $newObservation,
        ]);

        return back()->with('success', 'Observação adicionada com sucesso.');
    }
}
