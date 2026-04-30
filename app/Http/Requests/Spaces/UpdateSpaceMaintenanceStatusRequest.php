<?php

namespace App\Http\Requests\Spaces;

use App\Models\SpaceMaintenanceRecord;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSpaceMaintenanceStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('spaceMaintenance'));
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(SpaceMaintenanceRecord::STATUSES)],
        ];
    }
}
