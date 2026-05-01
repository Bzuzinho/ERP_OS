<?php

namespace App\Http\Requests\Hr;

use Illuminate\Foundation\Http\FormRequest;

class ValidateAttendanceRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()?->can('validate', $this->record) ?? false;
    }

    public function rules(): array
    {
        return [];
    }
}
