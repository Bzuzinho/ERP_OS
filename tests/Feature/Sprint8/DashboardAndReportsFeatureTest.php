<?php

namespace Tests\Feature\Sprint8;

use App\Models\AttendanceRecord;
use App\Models\Contact;
use App\Models\Employee;
use App\Models\InventoryCategory;
use App\Models\InventoryItem;
use App\Models\InventoryLocation;
use App\Models\OperationalPlan;
use App\Models\Ticket;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\Feature\Concerns\BuildsUsersWithPermissions;
use Tests\TestCase;

class DashboardAndReportsFeatureTest extends TestCase
{
    use BuildsUsersWithPermissions;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            OrganizationSeeder::class,
            RoleAndPermissionSeeder::class,
        ]);
    }

    public function test_super_admin_consegue_aceder_reports_index(): void
    {
        $admin = $this->makeSuperAdmin();

        $this->actingAs($admin)
            ->get(route('admin.reports.index'))
            ->assertOk();
    }

    public function test_utilizador_sem_reports_view_nao_consegue_aceder_reports(): void
    {
        $user = $this->makePortalUser('cidadao');
        $user->givePermissionTo('admin.access');

        $this->actingAs($user)
            ->get(route('admin.reports.index'))
            ->assertForbidden();
    }

    public function test_utilizador_com_reports_tickets_consegue_aceder_ticket_report(): void
    {
        $admin = $this->makeAdminWithPermissions(['reports.view', 'reports.tickets']);

        $this->actingAs($admin)
            ->get(route('admin.reports.tickets'))
            ->assertOk();
    }

    public function test_utilizador_sem_reports_inventory_nao_consegue_aceder_inventory_report(): void
    {
        $admin = $this->makePortalUser('administrativo');

        $this->actingAs($admin)
            ->get(route('admin.reports.inventory'))
            ->assertForbidden();
    }

    public function test_ticket_report_devolve_kpis_corretos_com_dados_minimos(): void
    {
        $admin = $this->makeAdminWithPermissions(['reports.view', 'reports.tickets']);

        Ticket::factory()->create([
            'organization_id' => $admin->organization_id,
            'created_by' => $admin->id,
            'status' => 'novo',
            'priority' => 'urgent',
            'due_date' => now()->subDay()->toDateString(),
        ]);

        Ticket::factory()->create([
            'organization_id' => $admin->organization_id,
            'created_by' => $admin->id,
            'status' => 'fechado',
            'closed_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.reports.tickets'));

        $response->assertOk();
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Admin/Reports/Tickets')
            ->where('summary.total', 2)
            ->where('summary.open', 1)
            ->where('summary.closed', 1)
            ->where('summary.urgent', 1)
            ->where('summary.overdue', 1)
        );
    }

    public function test_inventory_report_identifica_low_stock(): void
    {
        $admin = $this->makeAdminWithPermissions(['reports.view', 'reports.inventory']);

        $category = InventoryCategory::factory()->create(['organization_id' => $admin->organization_id]);
        $location = InventoryLocation::factory()->create(['organization_id' => $admin->organization_id]);

        InventoryItem::factory()->create([
            'organization_id' => $admin->organization_id,
            'inventory_category_id' => $category->id,
            'inventory_location_id' => $location->id,
            'current_stock' => 2,
            'minimum_stock' => 5,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.reports.inventory'));

        $response->assertOk();
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Admin/Reports/Inventory')
            ->where('summary.low_stock', 1)
        );
    }

    public function test_hr_report_identifica_presentes_e_ausentes_hoje(): void
    {
        $admin = $this->makeAdminWithPermissions(['reports.view', 'reports.hr']);

        $employeePresent = Employee::factory()->create(['organization_id' => $admin->organization_id]);
        $employeeAbsent = Employee::factory()->create(['organization_id' => $admin->organization_id]);

        AttendanceRecord::factory()->create([
            'organization_id' => $admin->organization_id,
            'employee_id' => $employeePresent->id,
            'date' => now()->toDateString(),
            'status' => 'present',
        ]);

        AttendanceRecord::factory()->create([
            'organization_id' => $admin->organization_id,
            'employee_id' => $employeeAbsent->id,
            'date' => now()->toDateString(),
            'status' => 'absent',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.reports.hr'));

        $response->assertOk();
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Admin/Reports/Hr')
            ->where('summary.present_today', 1)
            ->where('summary.absent_today', 1)
        );
    }

    public function test_planning_report_mostra_planos_em_execucao(): void
    {
        $admin = $this->makeAdminWithPermissions(['reports.view', 'reports.planning']);

        OperationalPlan::factory()->create([
            'organization_id' => $admin->organization_id,
            'created_by' => $admin->id,
            'owner_user_id' => $admin->id,
            'status' => 'in_progress',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.reports.planning'));

        $response->assertOk();
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Admin/Reports/Planning')
            ->where('summary.in_progress', 1)
        );
    }

    public function test_portal_dashboard_nao_mostra_tickets_de_outro_utilizador(): void
    {
        $user = $this->makePortalUser('cidadao');
        $otherUser = $this->makePortalUser('cidadao', $user->organization);

        $myContact = Contact::factory()->create([
            'organization_id' => $user->organization_id,
            'user_id' => $user->id,
        ]);

        Ticket::factory()->create([
            'organization_id' => $user->organization_id,
            'created_by' => $user->id,
            'contact_id' => $myContact->id,
            'title' => 'Meu pedido visível',
            'status' => 'novo',
        ]);

        Ticket::factory()->create([
            'organization_id' => $user->organization_id,
            'created_by' => $otherUser->id,
            'title' => 'Pedido privado de outro',
            'status' => 'novo',
        ]);

        $response = $this->actingAs($user)->get(route('portal.dashboard'));

        $response->assertOk();
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Portal/Dashboard/Index')
            ->has('data.tickets.active', 1)
            ->where('data.tickets.active.0.title', 'Meu pedido visível')
        );
    }

    public function test_export_csv_exige_reports_export(): void
    {
        $admin = $this->makePortalUser('administrativo');

        $this->actingAs($admin)
            ->get(route('admin.reports.export', [
                'report_type' => 'tickets',
                'format' => 'csv',
            ]))
            ->assertForbidden();
    }

    public function test_export_csv_devolve_content_type_correto(): void
    {
        $admin = $this->makeAdminWithPermissions(['reports.view', 'reports.export', 'reports.tickets']);

        Ticket::factory()->create([
            'organization_id' => $admin->organization_id,
            'created_by' => $admin->id,
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.reports.export', [
                'report_type' => 'tickets',
                'format' => 'csv',
            ]));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }
}
