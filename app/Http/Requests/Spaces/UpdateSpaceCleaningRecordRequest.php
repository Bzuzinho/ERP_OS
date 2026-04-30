<?php

namespace App\Http\Requests\Spaces;

use App\Models\SpaceCleaningRecord;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSpaceCleaningRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('spaceCleaning'));
    }

    public function rules(): array
    {
        return [
            'space_reservation_id' => ['nullable', 'exists:space_reservations,id'],
            'task_id' => ['nullable', 'exists:tasks,id'],
            'status' => ['sometimes', Rule::in(SpaceCleaningRecord::STATUSES)],
            'scheduled_at' => ['nullable', 'date'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
