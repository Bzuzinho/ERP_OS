<?php

namespace App\Policies;

use App\Models\MeetingMinute;
use App\Models\User;
use App\Support\OrganizationScope;

class MeetingMinutePolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->hasRole('super_admin') ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->can('meeting_minutes.view') || $user->hasAnyRole(['cidadao', 'associacao', 'empresa']);
    }

    public function view(User $user, MeetingMinute $meetingMinute): bool
    {
        if (! OrganizationScope::sameOrganization($meetingMinute->organization_id, $user)) {
            return false;
        }

        if ($user->can('meeting_minutes.view')) {
            return true;
        }

        if ($meetingMinute->status !== 'approved') {
            return false;
        }

        if (! $meetingMinute->document) {
            return false;
        }

        return $user->can('view', $meetingMinute->document);
    }

    public function create(User $user): bool
    {
        return $user->can('meeting_minutes.create');
    }

    public function update(User $user, MeetingMinute $meetingMinute): bool
    {
        return $user->can('meeting_minutes.update')
            && OrganizationScope::sameOrganization($meetingMinute->organization_id, $user);
    }

    public function approve(User $user, MeetingMinute $meetingMinute): bool
    {
        return ( $user->can('documents.approve') || $user->can('documents.manage_access') || $user->can('meeting_minutes.approve'))
            && OrganizationScope::sameOrganization($meetingMinute->organization_id, $user);
    }

    public function delete(User $user, MeetingMinute $meetingMinute): bool
    {
        return $user->can('meeting_minutes.delete')
            && OrganizationScope::sameOrganization($meetingMinute->organization_id, $user);
    }
}
