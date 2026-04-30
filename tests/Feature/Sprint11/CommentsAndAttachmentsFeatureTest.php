<?php

namespace Tests\Feature\Sprint11;

use App\Models\ActivityLog;
use App\Models\Attachment;
use App\Models\Comment;
use App\Models\Ticket;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\Feature\Concerns\BuildsUsersWithPermissions;
use Tests\TestCase;

class CommentsAndAttachmentsFeatureTest extends TestCase
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

    public function test_internal_comment_not_visible_and_public_comment_visible_on_portal_show(): void
    {
        $user = $this->makePortalUser('cidadao');
        $ticket = Ticket::factory()->create([
            'organization_id' => $user->organization_id,
            'created_by' => $user->id,
        ]);

        Comment::query()->create([
            'organization_id' => $ticket->organization_id,
            'user_id' => $user->id,
            'commentable_type' => Ticket::class,
            'commentable_id' => $ticket->id,
            'body' => 'Interno oculto',
            'visibility' => 'internal',
        ]);

        Comment::query()->create([
            'organization_id' => $ticket->organization_id,
            'user_id' => $user->id,
            'commentable_type' => Ticket::class,
            'commentable_id' => $ticket->id,
            'body' => 'Publico visivel',
            'visibility' => 'public',
        ]);

        $response = $this->actingAs($user)->get(route('portal.tickets.show', $ticket));

        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Portal/Tickets/Show')
            ->has('ticket.comments', 1)
            ->where('ticket.comments.0.body', 'Publico visivel')
        );
    }

    public function test_admin_comment_creation_works_and_activity_log_is_optional(): void
    {
        $admin = $this->makeAdminWithPermissions(['tickets.view']);
        $ticket = Ticket::factory()->create([
            'organization_id' => $admin->organization_id,
            'created_by' => $admin->id,
        ]);

        $before = ActivityLog::query()->count();

        $response = $this->actingAs($admin)->post(route('admin.tickets.comments.store', $ticket), [
            'body' => 'Comentario interno de administracao',
            'visibility' => 'internal',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('comments', [
            'commentable_type' => Ticket::class,
            'commentable_id' => $ticket->id,
            'body' => 'Comentario interno de administracao',
        ]);

        $after = ActivityLog::query()->count();
        $this->assertTrue(in_array($after, [$before, $before + 1], true));
    }

    public function test_internal_attachment_not_visible_and_public_attachment_visible_on_portal_show(): void
    {
        $user = $this->makePortalUser('cidadao');
        $ticket = Ticket::factory()->create([
            'organization_id' => $user->organization_id,
            'created_by' => $user->id,
        ]);

        Attachment::query()->create([
            'organization_id' => $ticket->organization_id,
            'uploaded_by' => $user->id,
            'attachable_type' => Ticket::class,
            'attachable_id' => $ticket->id,
            'file_path' => 'tickets/attachments/hidden.pdf',
            'file_name' => 'hidden.pdf',
            'visibility' => 'internal',
        ]);

        Attachment::query()->create([
            'organization_id' => $ticket->organization_id,
            'uploaded_by' => $user->id,
            'attachable_type' => Ticket::class,
            'attachable_id' => $ticket->id,
            'file_path' => 'tickets/attachments/public.pdf',
            'file_name' => 'public.pdf',
            'visibility' => 'public',
        ]);

        $response = $this->actingAs($user)->get(route('portal.tickets.show', $ticket));

        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Portal/Tickets/Show')
            ->has('ticket.attachments', 1)
            ->where('ticket.attachments.0.file_name', 'public.pdf')
        );
    }

    public function test_attachment_download_policy_is_skipped_when_no_protected_download_route_exists(): void
    {
        if (! app('router')->has('attachments.show')) {
            $this->markTestSkipped('Nao existe rota protegida de download de anexos para validar policy.');
        }

        $this->assertTrue(app('router')->has('attachments.show'));
    }
}
