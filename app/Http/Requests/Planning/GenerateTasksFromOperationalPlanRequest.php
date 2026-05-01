<?php

namespace App\Http\Requests\Planning;

use App\Models\Task;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GenerateTasksFromOperationalPlanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('planning.manage_tasks');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'tasks' => ['required', 'array', 'min:1'],
            'tasks.*.title' => ['required', 'string', 'max:255'],
            'tasks.*.description' => ['nullable', 'string'],
            'tasks.*.priority' => ['nullable', Rule::in(Task::PRIORITIES)],
            'tasks.*.start_date' => ['nullable', 'date'],
            'tasks.*.due_date' => ['nullable', 'date'],
            'tasks.*.assigned_to' => ['nullable', 'exists:users,id'],
            'tasks.*.position' => ['nullable', 'integer', 'min:0'],
            'tasks.*.is_milestone' => ['nullable', 'boolean'],
            'tasks.*.weight' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
