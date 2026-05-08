<?php

namespace Tests\Feature\SpaceReservations;

use App\Data\DashboardContext;
use App\Models\Organization;
use App\Models\Space;
use App\Models\SpaceReservation;
use App\Models\Task;
use App\Models\User;
use App\Services\Dashboard\OperationalDashboardService;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SpaceDashboardDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_returns_spaces_reservations_and_reservation_tasks_data(): void
    {
        $this->seed([
            OrganizationSeeder::class,
            RoleAndPermissionSeeder::class,
        ]);

        $organization = Organization::factory()->create();
        $user = User::factory()->create(['organization_id' => $organization->id]);

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

        $spaceInUse = Space::factory()->create([
            'organization_id' => $organization->id,
            'status' => 'available',
        ]);

        Space::factory()->create([
            'organization_id' => $organization->id,
            'status' => 'maintenance',
        ]);

        $todayReservation = SpaceReservation::factory()->create([
            'organization_id' => $organization->id,
            'space_id' => $spaceInUse->id,
            'status' => 'approved',
            'start_at' => now()->setHour(9)->setMinute(0),
            'end_at' => now()->setHour(11)->setMinute(0),
            'purpose' => 'Reuniao mensal',
        ]);

        SpaceReservation::factory()->create([
            'organization_id' => $organization->id,
            'space_id' => $spaceInUse->id,
            'status' => 'requested',
            'start_at' => now()->addDay()->setHour(14)->setMinute(0),
            'end_at' => now()->addDay()->setHour(16)->setMinute(0),
            'purpose' => 'Workshop',
        ]);

        Task::factory()->create([
            'organization_id' => $organization->id,
            'space_reservation_id' => $todayReservation->id,
            'status' => 'pending',
            'due_date' => now()->toDateString(),
            'title' => 'Preparar espaco',
        ]);

        $context = new DashboardContext($user, $organization->id, null, null, false);

        $service = app(OperationalDashboardService::class);
        $spacesData = $service->getEspacos($context);

        $this->assertSame(1, $spacesData['counts']['today_total']);
        $this->assertSame(1, $spacesData['counts']['pending_approval']);
        $this->assertSame(1, $spacesData['counts']['occupied_spaces_today']);
        $this->assertSame(1, $spacesData['counts']['spaces_in_maintenance']);
        $this->assertSame(1, $spacesData['counts']['reservation_tasks_today']);
        $this->assertCount(1, $spacesData['reservation_tasks']);
    }
}
