<?php

namespace Tests\Feature\Tickets;

use App\Models\Notification;
use App\Models\ServiceArea;
use App\Models\Task;
use App\Models\Ticket;
use App\Models\User;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\BuildsUsersWithPermissions;
use Tests\TestCase;

class TicketSubmitForValidationTest extends TestCase
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

    public function test_ticket_with_all_done_tasks_can_be_submitted_for_validation(): void
    {
        $user = $this->makeAdminWithPermissions(['tickets.submit-validation']);
        $ticket = Ticket::factory()->create([
            'organization_id' => $user->organization_id,
            'created_by' => $user->id,
            'assigned_to' => $user->id,
            'status' => 'com_tarefas',
        ]);

        Task::factory()->forTicket($ticket)->count(2)->create(['status' => 'done']);

        $this->actingAs($user)
            ->post(route('admin.tickets.submit-validation', $ticket), [
                'resolution_notes' => 'Execução operacional concluída.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'status' => 'aguarda_validacao',
            'resolution_notes' => 'Execução operacional concluída.',
        ]);

        $notification = Notification::query()->where('type', 'ticket_ready_for_validation')->latest('id')->firstOrFail();

        $this->assertDatabaseHas('notification_recipients', [
            'notification_id' => $notification->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_user_without_submit_validation_permission_cannot_submit_ticket_for_validation(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo(['admin.access', 'tickets.update']);

        $ticket = Ticket::factory()->create([
            'organization_id' => $user->organization_id,
            'created_by' => $user->id,
            'status' => 'com_tarefas',
        ]);

        Task::factory()->forTicket($ticket)->create(['status' => 'done']);

        $this->actingAs($user)
            ->post(route('admin.tickets.submit-validation', $ticket))
            ->assertForbidden();
    }

    public function test_user_with_submit_permission_but_without_validate_cannot_validate_ticket(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo(['admin.access', 'tickets.submit-validation']);

        $ticket = Ticket::factory()->create([
            'organization_id' => $user->organization_id,
            'created_by' => $user->id,
            'status' => 'aguarda_validacao',
        ]);

        $this->actingAs($user)
            ->post(route('admin.tickets.validate', $ticket), [
                'validation_notes' => 'Sem permissão.',
            ])
            ->assertForbidden();
    }

    public function test_operational_scope_is_respected_when_submitting_for_validation(): void
    {
        $organization = \App\Models\Organization::query()->firstOrFail();
        $validator = $this->makeAdminWithPermissions(['tickets.submit-validation'], $organization);
        $foreignArea = ServiceArea::query()->create([
            'organization_id' => $organization->id,
            'name' => 'Espaços Externos',
            'slug' => 'espacos-externos',
            'is_active' => true,
        ]);
        $scopedArea = ServiceArea::query()->create([
            'organization_id' => $organization->id,
            'name' => 'Operações',
            'slug' => 'operacoes',
            'is_active' => true,
        ]);

        $validator->serviceAreas()->attach($scopedArea->id, ['is_active' => true]);

        $ticket = Ticket::factory()->create([
            'organization_id' => $organization->id,
            'created_by' => $validator->id,
            'service_area_id' => $foreignArea->id,
            'status' => 'com_tarefas',
        ]);

        Task::factory()->forTicket($ticket)->create(['status' => 'done']);

        $this->actingAs($validator)
            ->post(route('admin.tickets.submit-validation', $ticket))
            ->assertStatus(403);
    }

    public function test_portal_ticket_validation_flow_keeps_portal_access_working(): void
    {
        $organization = \App\Models\Organization::query()->firstOrFail();
        $validator = $this->makeAdminWithPermissions(['tickets.validate'], $organization);
        $citizen = $this->makePortalUser('cidadao', $organization);

        $ticket = Ticket::factory()->create([
            'organization_id' => $organization->id,
            'created_by' => $citizen->id,
            'source' => 'portal',
            'status' => 'aguarda_validacao',
        ]);

        $this->actingAs($validator)
            ->post(route('admin.tickets.validate', $ticket), [
                'validation_notes' => 'Validado.',
                'resolution_notes' => 'Concluído.',
            ])
            ->assertRedirect();

        $this->actingAs($citizen)
            ->get(route('portal.tickets.show', $ticket))
            ->assertOk();
    }

    public function test_portal_creator_receives_submit_validation_notification_with_portal_route(): void
    {
        $organization = \App\Models\Organization::query()->firstOrFail();
        $validator = $this->makeAdminWithPermissions(['tickets.submit-validation'], $organization);
        $citizen = $this->makePortalUser('cidadao', $organization);

        $ticket = Ticket::factory()->create([
            'organization_id' => $organization->id,
            'created_by' => $citizen->id,
            'source' => 'portal',
            'status' => 'com_tarefas',
        ]);

        Task::factory()->forTicket($ticket)->create(['status' => 'done']);

        $this->actingAs($validator)
            ->post(route('admin.tickets.submit-validation', $ticket))
            ->assertRedirect();

        $notification = Notification::query()
            ->where('type', 'ticket_ready_for_validation')
            ->where('action_url', route('portal.tickets.show', $ticket, false))
            ->latest('id')
            ->firstOrFail();

        $this->assertDatabaseHas('notification_recipients', [
            'notification_id' => $notification->id,
            'user_id' => $citizen->id,
        ]);
    }
}