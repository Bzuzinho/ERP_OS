<?php

namespace Tests\Feature\Scopes;

use App\Models\Department;
use App\Models\Organization;
use App\Models\User;
use App\Services\Scopes\UserOperationalScopeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class UserDepartmentScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_utilizador_ve_apenas_departamentos_associados(): void
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->create(['organization_id' => $organization->id]);

        $depA = Department::factory()->create(['organization_id' => $organization->id]);
        $depB = Department::factory()->create(['organization_id' => $organization->id]);

        DB::table('organization_user')->insert([
            'organization_id' => $organization->id,
            'user_id' => $user->id,
            'role_context' => null,
            'has_global_access' => false,
            'is_default' => true,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('department_user')->insert([
            'organization_id' => $organization->id,
            'department_id' => $depA->id,
            'user_id' => $user->id,
            'role_context' => null,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $service = app(UserOperationalScopeService::class);
        $visible = $service->departmentsFor($user, $organization);

        $this->assertCount(1, $visible);
        $this->assertSame($depA->id, $visible->first()->id);
        $this->assertNotSame($depB->id, $visible->first()->id);
    }

    public function test_utilizador_com_has_global_access_ve_todos_os_departamentos_da_organizacao(): void
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->create(['organization_id' => $organization->id]);

        $depA = Department::factory()->create(['organization_id' => $organization->id]);
        $depB = Department::factory()->create(['organization_id' => $organization->id]);

        DB::table('organization_user')->insert([
            'organization_id' => $organization->id,
            'user_id' => $user->id,
            'role_context' => null,
            'has_global_access' => true,
            'is_default' => true,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $service = app(UserOperationalScopeService::class);
        $visible = $service->departmentsFor($user, $organization);

        $this->assertCount(2, $visible);
        $this->assertEqualsCanonicalizing([$depA->id, $depB->id], $visible->pluck('id')->all());
    }
}
