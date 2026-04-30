<?php

namespace App\Http\Requests\Spaces;

use App\Models\SpaceCleaningRecord;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSpaceCleaningRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', SpaceCleaningRecord::class);
    }

    public function rules(): array
    {
        return [
            'organization_id' => ['nullable', 'exists:organizations,id'],
            'space_id' => ['required', 'exists:spaces,id'],
            'space_reservation_id' => ['nullable', 'exists:space_reservations,id'],
            'task_id' => ['nullable', 'exists:tasks,id'],
            'status' => ['sometimes', Rule::in(SpaceCleaningRecord::STATUSES)],
            'scheduled_at' => ['nullable', 'date'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
