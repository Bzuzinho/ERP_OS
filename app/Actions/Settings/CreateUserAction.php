<?php

namespace App\Actions\Settings;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateUserAction
{
    public function execute(array $data): User
    {
        $password = (! empty($data['password']))
            ? $data['password']
            : Str::password(16);

        $user = User::create([
            'name'            => $data['name'],
            'email'           => $data['email'],
            'password'        => Hash::make($password),
            'organization_id' => $data['organization_id'] ?? null,
            'is_active'       => $data['is_active'] ?? true,
            'nif'             => $data['nif'] ?? null,
            'phone'           => $data['phone'] ?? null,
            'address'         => $data['address'] ?? null,
            'birth_date'      => $data['birth_date'] ?? null,
        ]);

        if (! empty($data['roles'])) {
            $user->syncRoles($data['roles']);
        }

        return $user;
    }
}
