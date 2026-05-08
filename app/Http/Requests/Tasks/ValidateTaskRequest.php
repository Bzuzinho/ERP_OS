<?php

namespace App\Http\Requests\Tasks;

use App\Models\Task;
use Illuminate\Foundation\Http\FormRequest;

class ValidateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        $task = $this->route('task');

        return $task instanceof Task && $this->user()->can('validate', $task);
    }

    public function rules(): array
    {
        return [
            'validation_notes' => ['nullable', 'string'],
        ];
    }
}
