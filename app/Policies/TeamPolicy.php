<?php

namespace App\Policies;

use App\Models\Team;
use App\Models\User;

class TeamPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('hr.view') || $user->hasPermissionTo('hr.manage_teams');
    }

    public function view(User $user, Team $team): bool
    {
        return $this->viewAny($user) && $user->organization_id === $team->organization_id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('hr.manage_teams');
    }

    public function update(User $user, Team $team): bool
    {
        return $this->create($user) && $user->organization_id === $team->organization_id;
    }

    public function delete(User $user, Team $team): bool
    {
        return $this->create($user) && $user->organization_id === $team->organization_id;
    }
}
