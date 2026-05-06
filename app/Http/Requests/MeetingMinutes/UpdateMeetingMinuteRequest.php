<?php

namespace App\Http\Requests\MeetingMinutes;

use App\Models\MeetingMinute;
use App\Support\OrganizationScope;
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
        $meetingMinute = $this->route('meetingMinute');
        $organizationId = $meetingMinute instanceof MeetingMinute ? $meetingMinute->organization_id : $this->user()?->organization_id;

        return [
            'event_id' => ['required', OrganizationScope::existsRuleForUser('events', $this->user(), organizationId: $organizationId)],
            'document_id' => ['nullable', OrganizationScope::existsRuleForUser('documents', $this->user(), organizationId: $organizationId)],
            'title' => ['required', 'string', 'max:255'],
            'summary' => ['nullable', 'string'],
            'status' => ['required', Rule::in(MeetingMinute::STATUSES)],
        ];
    }
}
