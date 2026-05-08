<?php

namespace Tests\Feature\Scopes;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class UserOrganizationScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_utilizador_pode_pertencer_a_varias_organizacoes(): void
    {
        $orgA = Organization::factory()->create();
        $orgB = Organization::factory()->create();

        $user = User::factory()->create(['organization_id' => $orgA->id]);

        DB::table('organization_user')->insert([
            [
                'organization_id' => $orgA->id,
                'user_id' => $user->id,
                'role_context' => null,
                'has_global_access' => false,
                'is_default' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'organization_id' => $orgB->id,
                'user_id' => $user->id,
                'role_context' => null,
                'has_global_access' => false,
                'is_default' => false,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $this->assertCount(2, $user->organizations()->get());
    }

    public function test_organizacao_default_e_respeitada(): void
    {
        $orgA = Organization::factory()->create();
        $orgB = Organization::factory()->create();

        $user = User::factory()->create(['organization_id' => $orgA->id]);

        DB::table('organization_user')->insert([
            [
                'organization_id' => $orgA->id,
                'user_id' => $user->id,
                'role_context' => null,
                'has_global_access' => false,
                'is_default' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'organization_id' => $orgB->id,
                'user_id' => $user->id,
                'role_context' => null,
                'has_global_access' => false,
                'is_default' => false,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $this->assertSame($orgA->id, $user->defaultOrganization()?->id);
    }

    public function test_users_organization_id_permanece_compativel(): void
    {
        $org = Organization::factory()->create();
        $user = User::factory()->create(['organization_id' => $org->id]);

        $this->assertSame($org->id, $user->defaultOrganization()?->id);
    }
}
