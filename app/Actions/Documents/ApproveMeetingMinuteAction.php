<?php

namespace App\Actions\Documents;

use App\Models\MeetingMinute;
use App\Models\User;
use App\Services\Tickets\ActivityLogger;

class ApproveMeetingMinuteAction
{
    public function __construct(
        private readonly ActivityLogger $activityLogger,
    ) {
    }

    public function execute(MeetingMinute $meetingMinute, User $user): MeetingMinute
    {
        $meetingMinute->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => $user->id,
        ]);

        $this->activityLogger->log(
            subject: $meetingMinute,
            action: 'meeting_minute.approved',
            user: $user,
            organization: $meetingMinute->organization,
            newValues: $meetingMinute->only(['status', 'approved_at', 'approved_by']),
            description: 'Ata aprovada.',
        );

        return $meetingMinute;
    }
}
