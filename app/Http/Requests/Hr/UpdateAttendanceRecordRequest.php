<?php

namespace App\Http\Requests\Hr;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAttendanceRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()?->can('update', $this->record) ?? false;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'string', 'in:present,absent,vacation,sick_leave,justified_absence,unjustified_absence,remote,off,training,overtime'],
            'check_in' => ['nullable', 'date_format:H:i'],
            'check_out' => ['nullable', 'date_format:H:i', 'after:check_in'],
            'break_minutes' => ['integer', 'min:0'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
