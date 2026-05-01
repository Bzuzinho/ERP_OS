<?php

namespace App\Http\Requests\Planning;

use Illuminate\Foundation\Http\FormRequest;

class ApproveOperationalPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('planning.approve');
    }

    public function rules(): array
    {
        return [];
    }
}
