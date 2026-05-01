<?php

namespace App\Policies;

use App\Models\AbsenceType;
use App\Models\User;

class AbsenceTypePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('hr.view');
    }

    public function view(User $user, AbsenceType $type): bool
    {
        return $this->viewAny($user) && $user->organization_id === $type->organization_id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('hr.update');
    }

    public function update(User $user, AbsenceType $type): bool
    {
        return $this->create($user) && $user->organization_id === $type->organization_id;
    }

    public function delete(User $user, AbsenceType $type): bool
    {
        return $this->create($user) && $user->organization_id === $type->organization_id;
    }
}
