<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\TaskChecklist;
use App\Models\TaskChecklistItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TaskChecklistItemController extends Controller
{
    public function store(Request $request, Task $task, TaskChecklist $checklist): RedirectResponse
    {
        $this->authorize('update', $task);

        abort_unless($checklist->task_id === $task->id, 404);

        $data = $request->validate([
            'label' => ['required', 'string', 'max:255'],
            'position' => ['nullable', 'integer', 'min:0'],
        ]);

        $checklist->items()->create($data);

        return back()->with('success', 'Item criado com sucesso.');
    }

    public function update(Request $request, Task $task, TaskChecklist $checklist, TaskChecklistItem $item): RedirectResponse
    {
        $this->authorize('update', $task);

        abort_unless($checklist->task_id === $task->id && $item->task_checklist_id === $checklist->id, 404);

        $data = $request->validate([
            'label' => ['required', 'string', 'max:255'],
            'position' => ['nullable', 'integer', 'min:0'],
            'is_completed' => ['sometimes', 'boolean'],
        ]);

        if (($data['is_completed'] ?? false) === true) {
            $data['completed_at'] = now();
            $data['completed_by'] = $request->user()->id;
        }

        if (($data['is_completed'] ?? false) === false) {
            $data['completed_at'] = null;
            $data['completed_by'] = null;
        }

        $item->update($data);

        return back()->with('success', 'Item atualizado com sucesso.');
    }

    public function destroy(Task $task, TaskChecklist $checklist, TaskChecklistItem $item): RedirectResponse
    {
        $this->authorize('update', $task);

        abort_unless($checklist->task_id === $task->id && $item->task_checklist_id === $checklist->id, 404);

        $item->delete();

        return back()->with('success', 'Item removido com sucesso.');
    }
}
