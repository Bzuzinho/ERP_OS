<?php

namespace App\Policies;

use App\Models\SpaceCleaningRecord;
use App\Models\User;

class SpaceCleaningRecordPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->hasRole('super_admin') ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->can('spaces.view') || $user->can('spaces.manage_cleaning');
    }

    public function view(User $user, SpaceCleaningRecord $spaceCleaningRecord): bool
    {
        return $user->can('spaces.view') || $user->can('spaces.manage_cleaning');
    }

    public function create(User $user): bool
    {
        return $user->can('spaces.manage_cleaning');
    }

    public function update(User $user, SpaceCleaningRecord $spaceCleaningRecord): bool
    {
        return $user->can('spaces.manage_cleaning');
    }

    public function delete(User $user, SpaceCleaningRecord $spaceCleaningRecord): bool
    {
        return $user->can('spaces.manage_cleaning');
    }
}
