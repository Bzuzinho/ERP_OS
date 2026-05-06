<?php

namespace App\Http\Requests\Tasks;

use App\Models\Task;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        $task = $this->route('task');

        return $task instanceof Task && $this->user()->can('update', $task);
    }

    public function rules(): array
    {
        return [
            'ticket_id' => ['nullable', 'exists:tickets,id'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['nullable', Rule::in(Task::STATUSES)],
            'priority' => ['required', Rule::in(Task::PRIORITIES)],
            'start_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date'],
        ];
    }
}
