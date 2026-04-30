<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\TaskChecklist;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TaskChecklistController extends Controller
{
    public function store(Request $request, Task $task): RedirectResponse
    {
        $this->authorize('update', $task);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'position' => ['nullable', 'integer', 'min:0'],
        ]);

        $task->checklists()->create($data);

        return back()->with('success', 'Checklist criada com sucesso.');
    }

    public function update(Request $request, Task $task, TaskChecklist $checklist): RedirectResponse
    {
        $this->authorize('update', $task);

        abort_unless($checklist->task_id === $task->id, 404);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'position' => ['nullable', 'integer', 'min:0'],
        ]);

        $checklist->update($data);

        return back()->with('success', 'Checklist atualizada com sucesso.');
    }

    public function destroy(Task $task, TaskChecklist $checklist): RedirectResponse
    {
        $this->authorize('update', $task);

        abort_unless($checklist->task_id === $task->id, 404);

        $checklist->delete();

        return back()->with('success', 'Checklist removida com sucesso.');
    }
}
