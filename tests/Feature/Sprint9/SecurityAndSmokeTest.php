<?php

namespace Tests\Feature\Sprint9;

use App\Models\Attachment;
use App\Models\Document;
use App\Models\Ticket;
use App\Models\User;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\BuildsUsersWithPermissions;
use Tests\TestCase;

/**
 * Sprint 9 — Smoke Tests e Testes de Segurança.
 *
 * Cobre:
 * - Respostas base das rotas principais
 * - Protecção de rotas admin
 * - Isolamento de dados entre utilizadores no portal
 * - Middleware EnsureUserIsActive
 * - Protecção de downloads de documentos/anexos
 */
class SecurityAndSmokeTest extends TestCase
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

    // -------------------------------------------------------------------------
    // Smoke Tests
    // -------------------------------------------------------------------------

    public function test_pagina_home_responde(): void
    {
        $this->get('/')->assertRedirect(route('login'));
    }

    public function test_pagina_login_responde(): void
    {
        $this->get(route('login'))->assertOk();
    }

    public function test_admin_dashboard_responde_para_super_admin(): void
    {
        $admin = $this->makeSuperAdmin();

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk();
    }

    public function test_portal_dashboard_responde_para_utilizador_autenticado(): void
    {
        $user = $this->makePortalUser();

        $this->actingAs($user)
            ->get(route('portal.dashboard'))
            ->assertOk();
    }

    // -------------------------------------------------------------------------
    // Protecção de rotas Admin
    // -------------------------------------------------------------------------

    public function test_admin_redireciona_visitante_nao_autenticado(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
    }

    public function test_admin_nega_acesso_a_utilizador_sem_admin_access(): void
    {
        $user = $this->makePortalUser('cidadao');

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_portal_redireciona_visitante_nao_autenticado(): void
    {
        $this->get(route('portal.dashboard'))->assertRedirect(route('login'));
    }

    public function test_utilizador_sem_reports_view_nao_acede_reports(): void
    {
        $user = $this->makePortalUser('cidadao');
        $user->givePermissionTo('admin.access');

        $this->actingAs($user)
            ->get(route('admin.reports.index'))
            ->assertForbidden();
    }

    public function test_utilizador_sem_hr_view_nao_acede_employees(): void
    {
        $user = $this->makePortalUser('cidadao');
        $user->givePermissionTo('admin.access');

        $this->actingAs($user)
            ->get(route('admin.hr.employees.index'))
            ->assertForbidden();
    }

    // -------------------------------------------------------------------------
    // Middleware EnsureUserIsActive
    // -------------------------------------------------------------------------

    public function test_utilizador_inativo_e_redirecionado_para_login(): void
    {
        $user = $this->makePortalUser();
        $user->update(['is_active' => false]);

        // Note: EnsureUserIsActive runs after auth, so user needs to be logged in
        $this->actingAs($user)
            ->get(route('portal.dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_utilizador_ativo_pode_aceder_portal(): void
    {
        $user = $this->makePortalUser();
        $user->update(['is_active' => true]);

        $this->actingAs($user)
            ->get(route('portal.dashboard'))
            ->assertOk();
    }

    // -------------------------------------------------------------------------
    // Isolamento de dados no portal
    // -------------------------------------------------------------------------

    public function test_portal_user_nao_ve_ticket_de_outro_utilizador(): void
    {
        $userA = $this->makePortalUser();
        $userB = $this->makePortalUser('cidadao', $userA->organization);

        $ticket = Ticket::factory()->create([
            'organization_id' => $userA->organization_id,
            'created_by' => $userA->id,
        ]);

        // userB should not be able to view userA's ticket
        $this->actingAs($userB)
            ->get(route('portal.tickets.show', $ticket))
            ->assertForbidden();
    }

    public function test_portal_user_ve_o_proprio_ticket(): void
    {
        $user = $this->makePortalUser();

        $ticket = Ticket::factory()->create([
            'organization_id' => $user->organization_id,
            'created_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->get(route('portal.tickets.show', $ticket))
            ->assertOk();
    }

    // -------------------------------------------------------------------------
    // Protecção de downloads de documentos
    // -------------------------------------------------------------------------

    public function test_download_documento_exige_autenticacao(): void
    {
        $doc = Document::factory()->create();

        $this->get(route('portal.documents.download', $doc))
            ->assertRedirect(route('login'));
    }

    public function test_admin_pode_fazer_download_de_documento_com_permissao(): void
    {
        $admin = $this->makeSuperAdmin();
        $doc = Document::factory()->create([
            'organization_id' => $admin->organization_id,
            'file_path' => 'documents/test/fake.pdf',
        ]);

        // We expect either 200 (if file exists) or a storage-related 404
        // The important thing is that auth + policy are checked (not 403)
        $response = $this->actingAs($admin)
            ->get(route('admin.documents.download', $doc));

        $this->assertNotEquals(401, $response->status());
        $this->assertNotEquals(403, $response->status());
    }

    public function test_portal_user_sem_permissao_nao_descarrega_documento_de_outro(): void
    {
        $userA = $this->makePortalUser();
        $userB = $this->makePortalUser('cidadao', $userA->organization);

        $doc = Document::factory()->create([
            'organization_id' => $userA->organization_id,
            'visibility' => 'internal',
        ]);

        $this->actingAs($userB)
            ->get(route('portal.documents.download', $doc))
            ->assertForbidden();
    }
}
