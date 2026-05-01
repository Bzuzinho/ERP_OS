<?php

namespace App\Http\Requests\Reports;

use Illuminate\Foundation\Http\FormRequest;

class ReportFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'status' => ['nullable', 'string'],
            'priority' => ['nullable', 'string'],
            'category' => ['nullable', 'string'],
            'department_id' => ['nullable', 'integer'],
            'user_id' => ['nullable', 'integer'],
            'employee_id' => ['nullable', 'integer'],
            'contact_id' => ['nullable', 'integer'],
            'space_id' => ['nullable', 'integer'],
            'inventory_item_id' => ['nullable', 'integer'],
            'plan_type' => ['nullable', 'string'],
            'search' => ['nullable', 'string', 'max:255'],
            'source' => ['nullable', 'string'],
            'assigned_to' => ['nullable', 'integer'],
        ];
    }
}
