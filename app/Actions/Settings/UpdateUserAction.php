<?php

namespace App\Actions\Settings;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class UpdateUserAction
{
    public function execute(User $user, array $data): User
    {
        $user->update([
            'name'            => $data['name'],
            'email'           => $data['email'],
            'organization_id' => $data['organization_id'] ?? $user->organization_id,
            'is_active'       => $data['is_active'] ?? $user->is_active,
            'nif'             => $data['nif'] ?? null,
            'phone'           => $data['phone'] ?? null,
            'address'         => $data['address'] ?? null,
            'birth_date'      => $data['birth_date'] ?? null,
        ]);

        return $user->fresh();
    }

    public function updateAvatar(User $user, UploadedFile $file): User
    {
        if ($user->avatar_path) {
            Storage::disk('public')->delete($user->avatar_path);
        }

        $path = $file->store('users/avatars', 'public');
        $user->update(['avatar_path' => $path]);

        return $user->fresh();
    }
}
