<?php

namespace App\Actions\Settings;

use App\Models\User;
use Illuminate\Validation\ValidationException;

class DeactivateUserAction
{
    public function execute(User $authUser, User $target): User
    {
        if ($authUser->id === $target->id) {
            throw ValidationException::withMessages([
                'user' => 'Não pode desativar a sua própria conta.',
            ]);
        }

        $target->update(['is_active' => false]);

        return $target->fresh();
    }
}
