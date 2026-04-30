<?php

namespace App\Http\Requests\MeetingMinutes;

use App\Models\MeetingMinute;
use Illuminate\Foundation\Http\FormRequest;

class ApproveMeetingMinuteRequest extends FormRequest
{
    public function authorize(): bool
    {
        $meetingMinute = $this->route('meetingMinute');

        if (! $meetingMinute instanceof MeetingMinute) {
            return false;
        }

        return $this->user()->can('approve', $meetingMinute);
    }

    public function rules(): array
    {
        return [];
    }
}
