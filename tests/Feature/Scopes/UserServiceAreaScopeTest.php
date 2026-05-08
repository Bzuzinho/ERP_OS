<?php

namespace Tests\Feature\Scopes;

use App\Models\Department;
use App\Models\Organization;
use App\Models\ServiceArea;
use App\Models\User;
use App\Services\Scopes\UserOperationalScopeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class UserServiceAreaScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_utilizador_ve_apenas_temas_associados(): void
    {
        $organization = Organization::factory()->create();
        $department = Department::factory()->create(['organization_id' => $organization->id]);
        $user = User::factory()->create(['organization_id' => $organization->id]);

        $allowed = ServiceArea::factory()->create([
            'organization_id' => $organization->id,
            'department_id' => $department->id,
        ]);

        ServiceArea::factory()->create([
            'organization_id' => $organization->id,
            'department_id' => $department->id,
        ]);

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

        DB::table('service_area_user')->insert([
            'service_area_id' => $allowed->id,
            'user_id' => $user->id,
            'organization_id' => $organization->id,
            'role' => null,
            'role_context' => null,
            'is_primary' => false,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $service = app(UserOperationalScopeService::class);
        $visible = $service->serviceAreasFor($user, $organization, $department);

        $this->assertCount(1, $visible);
        $this->assertSame($allowed->id, $visible->first()->id);
    }

    public function test_temas_respeitam_organizacao_e_departamento(): void
    {
        $orgA = Organization::factory()->create();
        $orgB = Organization::factory()->create();

        $depA = Department::factory()->create(['organization_id' => $orgA->id]);
        $depB = Department::factory()->create(['organization_id' => $orgB->id]);

        $user = User::factory()->create(['organization_id' => $orgA->id]);

        $areaInA = ServiceArea::factory()->create(['organization_id' => $orgA->id, 'department_id' => $depA->id]);
        $areaInB = ServiceArea::factory()->create(['organization_id' => $orgB->id, 'department_id' => $depB->id]);

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

        DB::table('service_area_user')->insert([
            [
                'service_area_id' => $areaInA->id,
                'user_id' => $user->id,
                'organization_id' => $orgA->id,
                'role' => null,
                'role_context' => null,
                'is_primary' => false,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'service_area_id' => $areaInB->id,
                'user_id' => $user->id,
                'organization_id' => $orgB->id,
                'role' => null,
                'role_context' => null,
                'is_primary' => false,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $service = app(UserOperationalScopeService::class);

        $visibleInA = $service->serviceAreasFor($user, $orgA, $depA);
        $visibleInB = $service->serviceAreasFor($user, $orgB, $depB);

        $this->assertSame([$areaInA->id], $visibleInA->pluck('id')->all());
        $this->assertSame([$areaInB->id], $visibleInB->pluck('id')->all());
    }

    public function test_arvore_parent_children_funciona(): void
    {
        $organization = Organization::factory()->create();
        $department = Department::factory()->create(['organization_id' => $organization->id]);

        $parent = ServiceArea::factory()->create([
            'organization_id' => $organization->id,
            'department_id' => $department->id,
            'parent_id' => null,
        ]);

        $child = ServiceArea::factory()->create([
            'organization_id' => $organization->id,
            'department_id' => $department->id,
            'parent_id' => $parent->id,
        ]);

        $this->assertSame($parent->id, $child->parent->id);
        $this->assertSame($child->id, $parent->children->first()->id);
    }
}
