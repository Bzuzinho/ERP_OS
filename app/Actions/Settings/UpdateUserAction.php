<?php

namespace App\Actions\Settings;

use App\Models\User;

class UpdateUserAction
{
    public function execute(User $user, array $data): User
    {
        $user->update([
            'name'            => $data['name'],
            'email'           => $data['email'],
            'organization_id' => $data['organization_id'] ?? $user->organization_id,
            'is_active'       => $data['is_active'] ?? $user->is_active,
        ]);

        return $user->fresh();
    }
}
