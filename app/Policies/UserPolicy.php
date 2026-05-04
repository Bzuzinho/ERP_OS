<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $authUser): bool
    {
        return $authUser->can('users.view');
    }

    public function view(User $authUser, User $target): bool
    {
        if (! $authUser->can('users.view')) {
            return false;
        }

        if ($authUser->hasRole('super_admin')) {
            return true;
        }

        return $authUser->organization_id === $target->organization_id;
    }

    public function create(User $authUser): bool
    {
        return $authUser->can('users.create');
    }

    public function update(User $authUser, User $target): bool
    {
        if (! $authUser->can('users.update')) {
            return false;
        }

        if ($authUser->hasRole('super_admin')) {
            return true;
        }

        return $authUser->organization_id === $target->organization_id;
    }

    public function delete(User $authUser, User $target): bool
    {
        return $authUser->can('users.delete') && $authUser->id !== $target->id;
    }

    public function manageRoles(User $authUser, User $target): bool
    {
        return $authUser->can('users.manage_roles') && $authUser->id !== $target->id;
    }

    public function resetPassword(User $authUser, User $target): bool
    {
        return $authUser->can('users.reset_password');
    }

    public function deactivate(User $authUser, User $target): bool
    {
        return $authUser->can('users.delete') && $authUser->id !== $target->id;
    }

    public function activate(User $authUser, User $target): bool
    {
        return $authUser->can('users.delete');
    }
}
