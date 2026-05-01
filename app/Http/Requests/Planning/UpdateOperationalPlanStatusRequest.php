<?php

namespace App\Http\Requests\Planning;

use App\Models\OperationalPlan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOperationalPlanStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('planning.update');
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(OperationalPlan::STATUSES)],
            'cancellation_reason' => ['nullable', 'string'],
        ];
    }
}
