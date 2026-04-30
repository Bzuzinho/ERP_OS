<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Tasks\CreateTaskAction;
use App\Actions\Tasks\CompleteTaskAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tasks\CompleteTaskRequest;
use App\Http\Requests\Tasks\StoreTaskRequest;
use App\Http\Requests\Tasks\UpdateTaskRequest;
use App\Models\Task;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TaskController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Task::class);

        $search = $request->string('search')->toString();
        $status = $request->string('status')->toString();
        $priority = $request->string('priority')->toString();
        $assignee = $request->string('assignee')->toString();

        $tasks = Task::query()
            ->with(['ticket:id,reference,title', 'assignee:id,name'])
            ->when($search, fn ($query) => $query
                ->where('title', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%"))
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($priority, fn ($query) => $query->where('priority', $priority))
            ->when($assignee, fn ($query) => $query->where('assigned_to', $assignee))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/Tasks/Index', [
            'tasks' => $tasks,
            'filters' => compact('search', 'status', 'priority', 'assignee'),
            'statuses' => Task::STATUSES,
            'priorities' => Task::PRIORITIES,
            'users' => User::query()->select('id', 'name')->orderBy('name')->get(),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Task::class);

        return Inertia::render('Admin/Tasks/Create', [
            'tickets' => Ticket::query()->select('id', 'reference', 'title')->latest()->limit(100)->get(),
            'users' => User::query()->select('id', 'name')->orderBy('name')->get(),
            'statuses' => Task::STATUSES,
            'priorities' => Task::PRIORITIES,
        ]);
    }

    public function store(StoreTaskRequest $request, CreateTaskAction $createTaskAction): RedirectResponse
    {
        $validated = $request->validated();

        $task = $createTaskAction->execute($request->user(), [
            ...$validated,
            'status' => $validated['status'] ?? 'pending',
        ]);

        return to_route('admin.tasks.show', $task)->with('success', 'Tarefa criada com sucesso.');
    }

    public function show(Task $task): Response
    {
        $this->authorize('view', $task);

        $task->load([
            'ticket:id,reference,title',
            'assignee:id,name',
            'creator:id,name',
            'completedBy:id,name',
            'checklists.items.completedBy:id,name',
            'comments.user:id,name',
            'attachments.uploader:id,name',
        ]);

        return Inertia::render('Admin/Tasks/Show', [
            'task' => $task,
            'statuses' => Task::STATUSES,
            'priorities' => Task::PRIORITIES,
        ]);
    }

    public function edit(Task $task): Response
    {
        $this->authorize('update', $task);

        return Inertia::render('Admin/Tasks/Edit', [
            'task' => $task,
            'tickets' => Ticket::query()->select('id', 'reference', 'title')->latest()->limit(100)->get(),
            'users' => User::query()->select('id', 'name')->orderBy('name')->get(),
            'priorities' => Task::PRIORITIES,
        ]);
    }

    public function update(UpdateTaskRequest $request, Task $task): RedirectResponse
    {
        $task->update($request->validated());

        return to_route('admin.tasks.show', $task)->with('success', 'Tarefa atualizada com sucesso.');
    }

    public function destroy(Task $task): RedirectResponse
    {
        $this->authorize('delete', $task);

        $task->delete();

        return to_route('admin.tasks.index')->with('success', 'Tarefa removida com sucesso.');
    }

    public function complete(CompleteTaskRequest $request, Task $task, CompleteTaskAction $completeTaskAction): RedirectResponse
    {
        $completeTaskAction->execute($task, $request->user());

        return back()->with('success', 'Tarefa concluida com sucesso.');
    }
}
