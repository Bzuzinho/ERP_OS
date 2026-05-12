<?php

namespace Tests\Feature\ResourceRequests;

use App\Data\DashboardContext;
use App\Models\Organization;
use App\Models\ResourceRequest;
use App\Services\Dashboard\OperationalDashboardService;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\BuildsUsersWithPermissions;
use Tests\TestCase;

class ResourceRequestDashboardDataTest extends TestCase
{
    use BuildsUsersWithPermissions;
    use RefreshDatabase;

    public function test_dashboard_exposes_resource_request_alerts_and_summary_counts(): void
    {
        $this->seed([OrganizationSeeder::class, RoleAndPermissionSeeder::class]);

        $org = Organization::factory()->create();
        $user = $this->makeAdminWithPermissions(['admin.access', 'resources.view'], $org);

        ResourceRequest::query()->create([
            'organization_id' => $org->id,
            'requested_by' => $user->id,
            'status' => 'requested',
            'title' => 'Pendente',
        ]);

        ResourceRequest::query()->create([
            'organization_id' => $org->id,
            'requested_by' => $user->id,
            'status' => 'approved',
            'title' => 'Aprovada',
        ]);

        $context = new DashboardContext(
            user: $user,
            organizationId: $org->id,
            departmentId: null,
            serviceAreaId: null,
            allMyScopes: false,
        );

        $service = app(OperationalDashboardService::class);

        $alertas = $service->getAlertas($context);
        $resumo = $service->getResumo($context);

        $this->assertArrayHasKey('pending_resource_requests', $alertas['counts']);
        $this->assertArrayHasKey('approved_resource_requests', $alertas['counts']);
        $this->assertSame(1, $alertas['counts']['pending_resource_requests']);
        $this->assertSame(1, $alertas['counts']['approved_resource_requests']);

        $this->assertArrayHasKey('pending_resource_requests', $resumo);
        $this->assertArrayHasKey('approved_resource_requests', $resumo);
        $this->assertSame(1, $resumo['pending_resource_requests']);
        $this->assertSame(1, $resumo['approved_resource_requests']);
    }
}
