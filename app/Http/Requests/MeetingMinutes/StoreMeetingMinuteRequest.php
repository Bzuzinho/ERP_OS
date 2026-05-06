<?php

namespace App\Http\Requests\MeetingMinutes;

use App\Models\MeetingMinute;
use App\Support\OrganizationScope;
use Illuminate\Foundation\Http\FormRequest;

class StoreMeetingMinuteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', MeetingMinute::class);
    }

    public function rules(): array
    {
        $organizationId = $this->user()?->organization_id;

        return [
            'event_id' => ['required', OrganizationScope::existsRuleForUser('events', $this->user(), organizationId: $organizationId)],
            'title' => ['required', 'string', 'max:255'],
            'summary' => ['nullable', 'string'],
            'document_id' => ['nullable', OrganizationScope::existsRuleForUser('documents', $this->user(), organizationId: $organizationId)],
            'document_type_id' => ['nullable', OrganizationScope::existsRuleForUser('document_types', $this->user(), organizationId: $organizationId)],
            'visibility' => ['nullable', 'string'],
            'file' => ['nullable', 'file', 'max:20480'],
        ];
    }
}
