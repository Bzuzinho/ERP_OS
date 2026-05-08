<?php

namespace Tests\Feature\Scopes;

use App\Models\Organization;
use App\Models\Ticket;
use App\Models\User;
use App\Services\Dashboard\AdminDashboardService;
use App\Services\Scopes\UserOperationalScopeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class DashboardContextValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_contexto_invalido_e_bloqueado(): void
    {
        $orgA = Organization::factory()->create();
        $orgB = Organization::factory()->create();

        $user = User::factory()->create(['organization_id' => $orgA->id]);

        DB::table('organization_user')->insert([
            'organization_id' => $orgA->id,
            'user_id' => $user->id,
            'role_context' => null,
            'has_global_access' => false,
            'is_default' => true,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $service = app(UserOperationalScopeService::class);

        $this->expectException(ValidationException::class);
        $service->validateContext($user, $orgB->id, null, null);
    }

    public function test_contexto_valido_e_aceite(): void
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->create(['organization_id' => $organization->id]);

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

        $service = app(UserOperationalScopeService::class);
        $context = $service->validateContext($user, $organization->id, null, null);

        $this->assertFalse($context->allMyScopes);
        $this->assertSame($organization->id, $context->organizationId);
    }

    public function test_all_my_scopes_agrega_apenas_escopos_permitidos(): void
    {
        $orgA = Organization::factory()->create();
        $orgB = Organization::factory()->create();
        $orgNotAllowed = Organization::factory()->create();

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

        Ticket::factory()->create(['organization_id' => $orgA->id, 'status' => 'novo', 'priority' => 'normal']);
        Ticket::factory()->create(['organization_id' => $orgB->id, 'status' => 'novo', 'priority' => 'normal']);
        Ticket::factory()->create(['organization_id' => $orgNotAllowed->id, 'status' => 'novo', 'priority' => 'normal']);

        $scopeService = app(UserOperationalScopeService::class);
        $dashboardService = app(AdminDashboardService::class);

        $context = $scopeService->validateContext($user, null, null, null);
        $data = $dashboardService->getDashboardDataForContext($context);

        $this->assertTrue($context->allMyScopes);
        $this->assertSame(2, $data['kpis']['open_tickets']);
    }
}
