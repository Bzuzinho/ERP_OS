<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $organization = Organization::firstOrFail();

        $user = User::updateOrCreate(
            ['email' => 'admin@juntaos.local'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('password'),
                'is_active' => true,
                'organization_id' => $organization->id,
            ],
        );

        $user->syncRoles(['super_admin']);
    }
}