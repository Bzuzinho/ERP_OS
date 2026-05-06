<?php

namespace Tests\Feature\Sprint12;

use App\Models\Notification;
use App\Models\NotificationRecipient;
use App\Models\ServiceArea;
use App\Models\Ticket;
use App\Models\User;
use App\Services\Notifications\NotificationService;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\Feature\Concerns\BuildsUsersWithPermissions;
use Tests\TestCase;

class NotificationsFeatureTest extends TestCase
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

    public function test_authenticated_user_sees_unread_count_in_inertia_props(): void
    {
        $user = $this->makePortalUser('cidadao');

        app(NotificationService::class)->createForUsers([$user], [
            'organization_id' => $user->organization_id,
            'type' => 'demo',
            'title' => 'Teste',
        ]);

        $response = $this->actingAs($user)->get(route('portal.dashboard'));

        $response->assertOk();
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->has('notifications')
            ->where('notifications.unread_count', 1)
        );
    }

    public function test_recent_notifications_list_contains_only_authenticated_user_items(): void
    {
        $user = $this->makePortalUser('cidadao');
        $other = $this->makePortalUser('cidadao', $user->organization);

        $notificationService = app(NotificationService::class);
        $notificationService->createForUsers([$user], [
            'organization_id' => $user->organization_id,
            'type' => 'demo',
            'title' => 'Minha notificacao',
        ]);
        $notificationService->createForUsers([$other], [
            'organization_id' => $user->organization_id,
            'type' => 'demo',
            'title' => 'Notificacao de outro',
        ]);

        $response = $this->actingAs($user)->get(route('portal.dashboard'));

        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->has('notifications.recent', 1)
            ->where('notifications.recent.0.title', 'Minha notificacao')
        );
    }

    public function test_user_does_not_see_other_users_notifications_in_portal_index(): void
    {
        $user = $this->makePortalUser('cidadao');
        $other = $this->makePortalUser('cidadao', $user->organization);

        app(NotificationService::class)->createForUsers([$user], [
            'organization_id' => $user->organization_id,
            'type' => 'demo',
            'title' => 'Somente meu',
        ]);

        app(NotificationService::class)->createForUsers([$other], [
            'organization_id' => $user->organization_id,
            'type' => 'demo',
            'title' => 'Nao devo ver',
        ]);

        $response = $this->actingAs($user)->get(route('portal.notifications.index'));

        $response->assertOk();
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Portal/Notifications/Index')
            ->has('notifications.data', 1)
            ->where('notifications.data.0.notification.title', 'Somente meu')
        );
    }

    public function test_creating_ticket_with_assigned_to_creates_notification_for_assignee(): void
    {
        $admin = $this->makeAdminWithPermissions(['tickets.create', 'notifications.view']);
        $assignee = User::factory()->create([
            'organization_id' => $admin->organization_id,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.tickets.store'), [
            'title' => 'Pedido atribuido',
            'description' => 'Descricao',
            'priority' => 'normal',
            'source' => 'internal',
            'visibility' => 'internal',
            'assigned_to' => $assignee->id,
        ]);

        $response->assertRedirect();

        $ticket = Ticket::query()->firstOrFail();

        $this->assertDatabaseHas('notifications', [
            'type' => 'ticket_created',
            'notifiable_type' => Ticket::class,
            'notifiable_id' => $ticket->id,
        ]);

        $this->assertDatabaseHas('notification_recipients', [
            'user_id' => $assignee->id,
        ]);
    }

    public function test_creating_ticket_with_service_area_notifies_area_users(): void
    {
        $admin = $this->makeAdminWithPermissions(['tickets.create', 'notifications.view']);
        $areaUser = User::factory()->create([
            'organization_id' => $admin->organization_id,
            'is_active' => true,
        ]);

        $serviceArea = ServiceArea::query()->create([
            'organization_id' => $admin->organization_id,
            'name' => 'Atendimento',
            'slug' => 'atendimento',
            'is_active' => true,
        ]);

        $serviceArea->users()->attach($areaUser->id);

        $response = $this->actingAs($admin)->post(route('admin.tickets.store'), [
            'title' => 'Pedido por area',
            'description' => 'Descricao',
            'priority' => 'normal',
            'source' => 'internal',
            'visibility' => 'internal',
            'service_area_id' => $serviceArea->id,
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('notification_recipients', [
            'user_id' => $areaUser->id,
        ]);
    }

    public function test_duplicate_recipients_are_removed(): void
    {
        $admin = $this->makeAdminWithPermissions(['tickets.create', 'notifications.view']);
        $target = User::factory()->create([
            'organization_id' => $admin->organization_id,
            'is_active' => true,
        ]);

        $serviceArea = ServiceArea::query()->create([
            'organization_id' => $admin->organization_id,
            'name' => 'Atendimento',
            'slug' => 'atendimento',
            'is_active' => true,
        ]);

        $serviceArea->users()->attach($target->id);

        $this->actingAs($admin)->post(route('admin.tickets.store'), [
            'title' => 'Pedido duplicado',
            'description' => 'Descricao',
            'priority' => 'normal',
            'source' => 'internal',
            'visibility' => 'internal',
            'assigned_to' => $target->id,
            'service_area_id' => $serviceArea->id,
        ])->assertRedirect();

        $notification = Notification::query()->latest('id')->firstOrFail();

        $this->assertSame(1, NotificationRecipient::query()
            ->where('notification_id', $notification->id)
            ->where('user_id', $target->id)
            ->count());
    }

    public function test_mark_notification_as_read_sets_read_at(): void
    {
        $admin = $this->makeAdminWithPermissions(['notifications.view']);

        app(NotificationService::class)->createForUsers([$admin], [
            'organization_id' => $admin->organization_id,
            'type' => 'demo',
            'title' => 'Ler noti',
        ]);

        $recipient = NotificationRecipient::query()->where('user_id', $admin->id)->firstOrFail();

        $this->actingAs($admin)
            ->post(route('admin.notifications.mark-read', $recipient))
            ->assertRedirect();

        $this->assertNotNull($recipient->fresh()->read_at);
    }

    public function test_mark_all_notifications_as_read_works(): void
    {
        $admin = $this->makeAdminWithPermissions(['notifications.view']);

        app(NotificationService::class)->createForUsers([$admin], [
            'organization_id' => $admin->organization_id,
            'type' => 'demo',
            'title' => 'N1',
        ]);
        app(NotificationService::class)->createForUsers([$admin], [
            'organization_id' => $admin->organization_id,
            'type' => 'demo',
            'title' => 'N2',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.notifications.mark-all-read'))
            ->assertRedirect();

        $this->assertSame(0, NotificationRecipient::query()->where('user_id', $admin->id)->whereNull('read_at')->count());
    }

    public function test_service_area_can_be_created_by_admin_junta(): void
    {
        $admin = $this->makeAdminWithPermissions(['service_areas.create']);

        $response = $this->actingAs($admin)->post(route('admin.service-areas.store'), [
            'name' => 'Executivo',
            'slug' => 'executivo',
            'description' => 'Area de decisao',
            'is_active' => true,
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('service_areas', [
            'organization_id' => $admin->organization_id,
            'slug' => 'executivo',
        ]);
    }

    public function test_user_without_permission_cannot_manage_service_areas(): void
    {
        $portalUser = $this->makePortalUser('cidadao');

        $this->actingAs($portalUser)
            ->get(route('admin.service-areas.index'))
            ->assertForbidden();
    }

    public function test_portal_user_only_sees_their_notifications(): void
    {
        $user = $this->makePortalUser('cidadao');
        $other = $this->makePortalUser('cidadao', $user->organization);

        app(NotificationService::class)->createForUsers([$user], [
            'organization_id' => $user->organization_id,
            'type' => 'demo',
            'title' => 'Visivel',
        ]);
        app(NotificationService::class)->createForUsers([$other], [
            'organization_id' => $user->organization_id,
            'type' => 'demo',
            'title' => 'Invisivel',
        ]);

        $this->actingAs($user)
            ->get(route('portal.notifications.index'))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('notifications.data', 1)
                ->where('notifications.data.0.notification.title', 'Visivel')
            );
    }

    public function test_marking_notification_requires_recipient_owned_by_authenticated_user(): void
    {
        $user = $this->makePortalUser('cidadao');
        $other = $this->makePortalUser('cidadao', $user->organization);

        app(NotificationService::class)->createForUsers([$user], [
            'organization_id' => $user->organization_id,
            'type' => 'demo',
            'title' => 'Nao e meu',
        ]);

        $recipient = NotificationRecipient::query()->where('user_id', $user->id)->firstOrFail();

        $this->actingAs($other)
            ->post(route('portal.notifications.mark-read', $recipient))
            ->assertForbidden();
    }
}
