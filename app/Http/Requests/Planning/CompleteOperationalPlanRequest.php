<?php

namespace App\Http\Requests\Planning;

use Illuminate\Foundation\Http\FormRequest;

class CompleteOperationalPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('planning.complete');
    }

    public function rules(): array
    {
        return [];
    }
}
