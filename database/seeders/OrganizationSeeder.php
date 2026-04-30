<?php

namespace Database\Seeders;

use App\Models\Organization;
use Illuminate\Database\Seeder;

class OrganizationSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Organization::updateOrCreate(
            ['slug' => 'junta-freguesia-demo'],
            [
                'name' => 'Junta de Freguesia Demo',
                'code' => 'JFD',
                'email' => 'geral@junta-demo.pt',
                'is_active' => true,
            ],
        );
    }
}