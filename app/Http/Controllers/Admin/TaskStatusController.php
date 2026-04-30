<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Tasks\UpdateTaskStatusAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tasks\UpdateTaskStatusRequest;
use App\Models\Task;
use Illuminate\Http\RedirectResponse;

class TaskStatusController extends Controller
{
    public function update(UpdateTaskStatusRequest $request, Task $task, UpdateTaskStatusAction $updateTaskStatusAction): RedirectResponse
    {
        $updateTaskStatusAction->execute($task, $request->validated('status'), $request->user());

        return back()->with('success', 'Estado da tarefa atualizado com sucesso.');
    }
}
