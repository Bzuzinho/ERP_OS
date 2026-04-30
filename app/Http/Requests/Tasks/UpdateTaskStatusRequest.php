<?php

namespace App\Http\Requests\Tasks;

use App\Models\Task;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTaskStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        $task = $this->route('task');

        return $task instanceof Task && ($this->user()->can('update', $task) || $this->user()->can('complete', $task));
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(Task::STATUSES)],
        ];
    }
}
