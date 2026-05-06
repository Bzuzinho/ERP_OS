<?php

namespace Tests\Feature\Tickets;

use App\Models\Attachment;
use App\Models\Comment;
use App\Models\Notification;
use App\Models\NotificationRecipient;
use App\Models\Organization;
use App\Models\Ticket;
use App\Models\User;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia;
use Tests\Feature\Concerns\BuildsUsersWithPermissions;
use Tests\TestCase;

class TicketCommunicationFlowTest extends TestCase
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

    public function test_admin_pode_adicionar_nota_interna_a_ticket(): void
    {
        $admin = $this->makeAdminWithPermissions(['tickets.view']);
        $ticket = Ticket::factory()->create([
            'organization_id' => $admin->organization_id,
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.tickets.comments.store', $ticket), [
                'body' => 'Nota interna de validacao',
                'visibility' => 'internal',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('comments', [
            'commentable_type' => Ticket::class,
            'commentable_id' => $ticket->id,
            'visibility' => 'internal',
            'body' => 'Nota interna de validacao',
        ]);
    }

    public function test_portal_nao_ve_nota_interna(): void
    {
        $citizen = $this->makePortalUser('cidadao');
        $ticket = Ticket::factory()->create([
            'organization_id' => $citizen->organization_id,
            'created_by' => $citizen->id,
            'source' => 'portal',
        ]);

        Comment::query()->create([
            'organization_id' => $ticket->organization_id,
            'user_id' => $citizen->id,
            'commentable_type' => Ticket::class,
            'commentable_id' => $ticket->id,
            'body' => 'Nota interna invisivel',
            'visibility' => 'internal',
        ]);

        Comment::query()->create([
            'organization_id' => $ticket->organization_id,
            'user_id' => $citizen->id,
            'commentable_type' => Ticket::class,
            'commentable_id' => $ticket->id,
            'body' => 'Mensagem publica visivel',
            'visibility' => 'public',
        ]);

        $this->actingAs($citizen)
            ->get(route('portal.tickets.show', $ticket))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Portal/Tickets/Show')
                ->has('ticket.comments', 1)
                ->where('ticket.comments.0.body', 'Mensagem publica visivel')
            );
    }

    public function test_admin_pode_adicionar_resposta_publica(): void
    {
        $organization = Organization::factory()->create();
        $admin = $this->makeAdminWithPermissions(['tickets.view'], $organization);
        $citizen = $this->makePortalUser('cidadao', $organization);
        $ticket = Ticket::factory()->create([
            'organization_id' => $organization->id,
            'created_by' => $citizen->id,
            'source' => 'portal',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.tickets.comments.store', $ticket), [
                'body' => 'Resposta publica da junta',
                'visibility' => 'public',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('comments', [
            'commentable_type' => Ticket::class,
            'commentable_id' => $ticket->id,
            'visibility' => 'public',
            'body' => 'Resposta publica da junta',
        ]);
    }

    public function test_portal_ve_resposta_publica(): void
    {
        $organization = Organization::factory()->create();
        $admin = $this->makeAdminWithPermissions(['tickets.view'], $organization);
        $citizen = $this->makePortalUser('cidadao', $organization);
        $ticket = Ticket::factory()->create([
            'organization_id' => $organization->id,
            'created_by' => $citizen->id,
            'source' => 'portal',
        ]);

        Comment::query()->create([
            'organization_id' => $ticket->organization_id,
            'user_id' => $admin->id,
            'commentable_type' => Ticket::class,
            'commentable_id' => $ticket->id,
            'body' => 'Retorno oficial da Junta',
            'visibility' => 'public',
        ]);

        $this->actingAs($citizen)
            ->get(route('portal.tickets.show', $ticket))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Portal/Tickets/Show')
                ->where('ticket.comments.0.body', 'Retorno oficial da Junta')
            );
    }

    public function test_resposta_publica_gera_notificacao_ao_municipe(): void
    {
        $organization = Organization::factory()->create();
        $admin = $this->makeAdminWithPermissions(['tickets.view'], $organization);
        $citizen = $this->makePortalUser('cidadao', $organization);
        $ticket = Ticket::factory()->create([
            'organization_id' => $organization->id,
            'created_by' => $citizen->id,
            'source' => 'portal',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.tickets.comments.store', $ticket), [
                'body' => 'Nova resposta publica',
                'visibility' => 'public',
            ])
            ->assertRedirect();

        $notification = Notification::query()->where('type', 'ticket_public_reply_added')->latest('id')->firstOrFail();

        $this->assertDatabaseHas('notification_recipients', [
            'notification_id' => $notification->id,
            'user_id' => $citizen->id,
        ]);

        $this->assertSame(route('portal.tickets.show', $ticket, false), $notification->action_url);
    }

    public function test_comentario_do_municipe_gera_notificacao_interna(): void
    {
        $organization = Organization::factory()->create();
        $citizen = $this->makePortalUser('cidadao', $organization);
        $assignee = $this->makeAdminWithPermissions(['tickets.view'], $organization);

        $ticket = Ticket::factory()->create([
            'organization_id' => $organization->id,
            'created_by' => $citizen->id,
            'assigned_to' => $assignee->id,
            'source' => 'portal',
        ]);

        $this->actingAs($citizen)
            ->post(route('portal.tickets.comments.store', $ticket), [
                'body' => 'Tenho mais informacoes para juntar.',
            ])
            ->assertRedirect();

        $notification = Notification::query()->where('type', 'ticket_citizen_reply_added')->latest('id')->firstOrFail();

        $this->assertDatabaseHas('notification_recipients', [
            'notification_id' => $notification->id,
            'user_id' => $assignee->id,
        ]);

        $this->assertSame(route('admin.tickets.show', $ticket, false), $notification->action_url);
    }

    public function test_portal_nao_consegue_criar_comentario_interno(): void
    {
        $citizen = $this->makePortalUser('cidadao');
        $ticket = Ticket::factory()->create([
            'organization_id' => $citizen->organization_id,
            'created_by' => $citizen->id,
            'source' => 'portal',
        ]);

        $this->actingAs($citizen)
            ->from(route('portal.tickets.show', $ticket))
            ->post(route('portal.tickets.comments.store', $ticket), [
                'body' => 'Tentativa de nota interna',
                'visibility' => 'internal',
            ])
            ->assertSessionHasErrors('visibility');
    }

    public function test_admin_pode_carregar_anexo_interno(): void
    {
        Storage::fake('local');

        $admin = $this->makeAdminWithPermissions(['tickets.view']);
        $ticket = Ticket::factory()->create([
            'organization_id' => $admin->organization_id,
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.tickets.attachments.store', $ticket), [
                'file' => UploadedFile::fake()->create('interno.pdf', 20),
                'visibility' => 'internal',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('attachments', [
            'attachable_type' => Ticket::class,
            'attachable_id' => $ticket->id,
            'visibility' => 'internal',
        ]);
    }

    public function test_portal_nao_ve_nem_descarrega_anexo_interno(): void
    {
        Storage::fake('local');

        $citizen = $this->makePortalUser('cidadao');
        $ticket = Ticket::factory()->create([
            'organization_id' => $citizen->organization_id,
            'created_by' => $citizen->id,
            'source' => 'portal',
        ]);

        Storage::disk('local')->put('tickets/attachments/interno.pdf', 'conteudo');

        $attachment = Attachment::query()->create([
            'organization_id' => $ticket->organization_id,
            'uploaded_by' => $citizen->id,
            'attachable_type' => Ticket::class,
            'attachable_id' => $ticket->id,
            'file_path' => 'tickets/attachments/interno.pdf',
            'file_name' => 'interno.pdf',
            'mime_type' => 'application/pdf',
            'size' => 10,
            'visibility' => 'internal',
        ]);

        $this->actingAs($citizen)
            ->get(route('portal.tickets.show', $ticket))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Portal/Tickets/Show')
                ->has('ticket.attachments', 0)
            );

        $this->actingAs($citizen)
            ->get(route('portal.attachments.download', $attachment))
            ->assertForbidden();
    }

    public function test_admin_pode_carregar_anexo_publico_e_portal_pode_ver_descarregar(): void
    {
        Storage::fake('local');

        $organization = Organization::factory()->create();
        $admin = $this->makeAdminWithPermissions(['tickets.view'], $organization);
        $citizen = $this->makePortalUser('cidadao', $organization);

        $ticket = Ticket::factory()->create([
            'organization_id' => $organization->id,
            'created_by' => $citizen->id,
            'source' => 'portal',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.tickets.attachments.store', $ticket), [
                'file' => UploadedFile::fake()->create('publico.pdf', 20),
                'visibility' => 'public',
            ])
            ->assertRedirect();

        $attachment = Attachment::query()->latest('id')->firstOrFail();

        $this->actingAs($citizen)
            ->get(route('portal.tickets.show', $ticket))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Portal/Tickets/Show')
                ->has('ticket.attachments', 1)
                ->where('ticket.attachments.0.id', $attachment->id)
            );

        $download = $this->actingAs($citizen)->get(route('portal.attachments.download', $attachment));
        $this->assertSame(200, $download->getStatusCode());
    }

    public function test_mudanca_de_estado_regista_historico(): void
    {
        $admin = $this->makeAdminWithPermissions(['tickets.update']);
        $ticket = Ticket::factory()->create([
            'organization_id' => $admin->organization_id,
            'created_by' => $admin->id,
            'status' => 'novo',
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.tickets.status.update', $ticket), [
                'status' => 'em_analise',
                'notes' => 'Analise iniciada',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('ticket_status_histories', [
            'ticket_id' => $ticket->id,
            'old_status' => 'novo',
            'new_status' => 'em_analise',
        ]);
    }

    public function test_mudanca_de_estado_relevante_gera_notificacao_ao_municipe(): void
    {
        $organization = Organization::factory()->create();
        $admin = $this->makeAdminWithPermissions(['tickets.update'], $organization);
        $citizen = $this->makePortalUser('cidadao', $organization);

        $ticket = Ticket::factory()->create([
            'organization_id' => $organization->id,
            'created_by' => $citizen->id,
            'status' => 'novo',
            'source' => 'portal',
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.tickets.status.update', $ticket), [
                'status' => 'em_analise',
            ])
            ->assertRedirect();

        $notification = Notification::query()->where('type', 'ticket_status_changed')->latest('id')->firstOrFail();

        $this->assertDatabaseHas('notification_recipients', [
            'notification_id' => $notification->id,
            'user_id' => $citizen->id,
        ]);
    }

    public function test_comentario_e_anexo_cross_org_retorna_403_ou_404(): void
    {
        Storage::fake('local');

        $orgA = Organization::factory()->create();
        $orgB = Organization::factory()->create();

        $adminA = $this->makeAdminWithPermissions(['tickets.view'], $orgA);
        $ticketB = Ticket::factory()->create([
            'organization_id' => $orgB->id,
            'created_by' => User::factory()->create(['organization_id' => $orgB->id])->id,
        ]);

        $commentResponse = $this->actingAs($adminA)
            ->post(route('admin.tickets.comments.store', $ticketB), [
                'body' => 'Tentativa cross org',
                'visibility' => 'internal',
            ]);

        $this->assertContains($commentResponse->status(), [403, 404]);

        Storage::disk('local')->put('tickets/attachments/cross.pdf', 'abc');

        $attachment = Attachment::query()->create([
            'organization_id' => $orgB->id,
            'uploaded_by' => User::factory()->create(['organization_id' => $orgB->id])->id,
            'attachable_type' => Ticket::class,
            'attachable_id' => $ticketB->id,
            'file_path' => 'tickets/attachments/cross.pdf',
            'file_name' => 'cross.pdf',
            'visibility' => 'public',
        ]);

        $downloadResponse = $this->actingAs($adminA)->get(route('admin.attachments.download', $attachment));
        $this->assertContains($downloadResponse->status(), [403, 404]);
    }

    public function test_action_url_do_municipe_aponta_para_portal_e_interno_para_admin(): void
    {
        $organization = Organization::factory()->create();
        $admin = $this->makeAdminWithPermissions(['tickets.view'], $organization);
        $citizen = $this->makePortalUser('cidadao', $organization);

        $ticket = Ticket::factory()->create([
            'organization_id' => $organization->id,
            'created_by' => $citizen->id,
            'assigned_to' => $admin->id,
            'source' => 'portal',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.tickets.comments.store', $ticket), [
                'body' => 'Resposta publica para o municipe',
                'visibility' => 'public',
            ])
            ->assertRedirect();

        $publicNotification = Notification::query()->where('type', 'ticket_public_reply_added')->latest('id')->firstOrFail();
        $this->assertSame(route('portal.tickets.show', $ticket, false), $publicNotification->action_url);

        $this->actingAs($citizen)
            ->post(route('portal.tickets.comments.store', $ticket), [
                'body' => 'Resposta do municipe para equipa interna',
            ])
            ->assertRedirect();

        $internalNotification = Notification::query()->where('type', 'ticket_citizen_reply_added')->latest('id')->firstOrFail();
        $this->assertSame(route('admin.tickets.show', $ticket, false), $internalNotification->action_url);

        $this->assertGreaterThan(0, NotificationRecipient::query()->where('notification_id', $internalNotification->id)->count());
    }

    public function test_resposta_do_municipe_notifica_super_admin_no_fallback_interno(): void
    {
        $organization = Organization::factory()->create();
        $superAdmin = $this->makeSuperAdmin($organization);
        $citizen = $this->makePortalUser('cidadao', $organization);

        $ticket = Ticket::factory()->create([
            'organization_id' => $organization->id,
            'created_by' => $citizen->id,
            'assigned_to' => null,
            'service_area_id' => null,
            'department_id' => null,
            'team_id' => null,
            'source' => 'portal',
        ]);

        $this->actingAs($citizen)
            ->post(route('portal.tickets.comments.store', $ticket), [
                'body' => 'Atualizacao do municipe sem atribuicao.',
            ])
            ->assertRedirect();

        $notification = Notification::query()->where('type', 'ticket_citizen_reply_added')->latest('id')->firstOrFail();

        $this->assertDatabaseHas('notification_recipients', [
            'notification_id' => $notification->id,
            'user_id' => $superAdmin->id,
        ]);
    }
}
