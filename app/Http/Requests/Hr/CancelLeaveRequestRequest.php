<?php

namespace App\Http\Requests\Hr;

use Illuminate\Foundation\Http\FormRequest;

class CancelLeaveRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()?->can('delete', $this->leaveRequest) ?? false;
    }

    public function rules(): array
    {
        return [
            'cancellation_reason' => ['nullable', 'string'],
        ];
    }
}
