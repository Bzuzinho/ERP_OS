<?php

namespace App\Http\Requests\MeetingMinutes;

use App\Models\MeetingMinute;
use Illuminate\Foundation\Http\FormRequest;

class StoreMeetingMinuteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', MeetingMinute::class);
    }

    public function rules(): array
    {
        return [
            'event_id' => ['required', 'exists:events,id'],
            'title' => ['required', 'string', 'max:255'],
            'summary' => ['nullable', 'string'],
            'document_id' => ['nullable', 'exists:documents,id'],
            'document_type_id' => ['nullable', 'exists:document_types,id'],
            'visibility' => ['nullable', 'string'],
            'file' => ['nullable', 'file', 'max:20480'],
        ];
    }
}
