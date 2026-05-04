<?php

namespace App\Actions\Settings;

use App\Models\User;
use Illuminate\Validation\ValidationException;

class UpdateUserRolesAction
{
    public function execute(User $authUser, User $target, array $roles): User
    {
        // Protect super_admin assignment/removal
        $hasSuperAdmin = in_array('super_admin', $roles, true);
        $currentlyHasSuperAdmin = $target->hasRole('super_admin');

        if (($hasSuperAdmin || $currentlyHasSuperAdmin) && ! $authUser->hasRole('super_admin')) {
            throw ValidationException::withMessages([
                'roles' => 'Apenas o super_admin pode atribuir ou remover o perfil super_admin.',
            ]);
        }

        $target->syncRoles($roles);

        return $target->fresh();
    }
}
