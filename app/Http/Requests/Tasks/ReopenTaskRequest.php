<?php

namespace App\Http\Requests\Tasks;

use App\Models\Task;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReopenTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        $task = $this->route('task');

        return $task instanceof Task && $this->user()->can('reopen', $task);
    }

    public function rules(): array
    {
        return [
            'reason' => ['nullable', 'string'],
            'target_status' => ['nullable', Rule::in(['reopened', 'in_progress'])],
        ];
    }
}
