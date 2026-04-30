<?php

namespace App\Http\Requests\Spaces;

use App\Models\SpaceMaintenanceRecord;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSpaceMaintenanceRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('spaceMaintenance'));
    }

    public function rules(): array
    {
        return [
            'ticket_id' => ['nullable', 'exists:tickets,id'],
            'task_id' => ['nullable', 'exists:tasks,id'],
            'type' => ['required', Rule::in(SpaceMaintenanceRecord::TYPES)],
            'status' => ['sometimes', Rule::in(SpaceMaintenanceRecord::STATUSES)],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'scheduled_at' => ['nullable', 'date'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
