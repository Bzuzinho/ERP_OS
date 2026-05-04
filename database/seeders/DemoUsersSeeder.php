<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoUsersSeeder extends Seeder
{
    public function run(): void
    {
        $organization = Organization::firstOrFail();

        $users = [
            ['email' => 'executivo@juntaos.local',     'name' => 'Executivo Demo',      'role' => 'executivo'],
            ['email' => 'administrativo@juntaos.local', 'name' => 'Administrativo Demo', 'role' => 'administrativo'],
            ['email' => 'operacional@juntaos.local',   'name' => 'Operacional Demo',    'role' => 'operacional'],
            ['email' => 'manutencao@juntaos.local',    'name' => 'Manutenção Demo',     'role' => 'manutencao'],
            ['email' => 'armazem@juntaos.local',       'name' => 'Armazém Demo',        'role' => 'armazem'],
            ['email' => 'rh@juntaos.local',            'name' => 'RH Demo',             'role' => 'rh'],
            ['email' => 'cidadao@juntaos.local',       'name' => 'Cidadão Demo',        'role' => 'cidadao'],
            ['email' => 'associacao@juntaos.local',    'name' => 'Associação Demo',     'role' => 'associacao'],
        ];

        foreach ($users as $data) {
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name'            => $data['name'],
                    'password'        => Hash::make('password'),
                    'is_active'       => true,
                    'organization_id' => $organization->id,
                ],
            );

            $user->syncRoles([$data['role']]);
        }
    }
}
