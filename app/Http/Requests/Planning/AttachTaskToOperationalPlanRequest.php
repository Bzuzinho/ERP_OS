<?php

namespace App\Http\Requests\Planning;

use Illuminate\Foundation\Http\FormRequest;

class AttachTaskToOperationalPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('planning.manage_tasks');
    }

    public function rules(): array
    {
        return [
            'task_id' => ['required', 'exists:tasks,id'],
            'position' => ['nullable', 'integer', 'min:0'],
            'is_milestone' => ['nullable', 'boolean'],
            'weight' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
