<?php

namespace Tests\Feature\Concerns;

use App\Models\Organization;
use App\Models\User;
use Spatie\Permission\Models\Role;

trait BuildsUsersWithPermissions
{
    protected function makeSuperAdmin(?Organization $organization = null): User
    {
        $organization ??= Organization::factory()->create();

        $user = User::factory()->create([
            'organization_id' => $organization->id,
        ]);

        $user->assignRole('super_admin');

        return $user;
    }

    protected function makeAdminWithPermissions(array $permissions, ?Organization $organization = null): User
    {
        $organization ??= Organization::factory()->create();

        $user = User::factory()->create([
            'organization_id' => $organization->id,
        ]);

        $user->assignRole('admin_junta');
        $user->givePermissionTo($permissions);

        return $user;
    }

    protected function makePortalUser(string $role = 'cidadao', ?Organization $organization = null): User
    {
        $organization ??= Organization::factory()->create();

        $user = User::factory()->create([
            'organization_id' => $organization->id,
        ]);

        if (! Role::query()->where('name', $role)->exists()) {
            Role::findOrCreate($role, 'web');
        }

        $user->assignRole($role);

        return $user;
    }
}
