<?php

namespace App\Http\Requests\MeetingMinutes;

use App\Models\MeetingMinute;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMeetingMinuteRequest extends FormRequest
{
    public function authorize(): bool
    {
        $meetingMinute = $this->route('meetingMinute');

        return $meetingMinute instanceof MeetingMinute && $this->user()->can('update', $meetingMinute);
    }

    public function rules(): array
    {
        return [
            'event_id' => ['required', 'exists:events,id'],
            'document_id' => ['nullable', 'exists:documents,id'],
            'title' => ['required', 'string', 'max:255'],
            'summary' => ['nullable', 'string'],
            'status' => ['required', Rule::in(MeetingMinute::STATUSES)],
        ];
    }
}
