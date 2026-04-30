<?php

namespace Tests\Feature\Sprint11;

use App\Models\Contact;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\BuildsUsersWithPermissions;
use Tests\TestCase;

class AdminContactsFeatureTest extends TestCase
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

    public function test_super_admin_can_list_contacts(): void
    {
        $admin = $this->makeSuperAdmin();
        Contact::factory()->count(3)->create(['organization_id' => $admin->organization_id]);

        $response = $this->actingAs($admin)->get(route('admin.contacts.index'));

        $response->assertOk();
    }

    public function test_user_without_contacts_view_cannot_access_contacts_index(): void
    {
        $admin = $this->makePortalUser('cidadao');
        $admin->givePermissionTo('admin.access');

        $response = $this->actingAs($admin)->get(route('admin.contacts.index'));

        $response->assertForbidden();
    }

    public function test_user_with_contacts_create_can_create_contact(): void
    {
        $admin = $this->makeAdminWithPermissions(['contacts.create', 'contacts.view']);

        $response = $this->actingAs($admin)->post(route('admin.contacts.store'), [
            'type' => 'citizen',
            'name' => 'Joao Silva',
            'email' => 'joao@example.test',
            'phone' => '210000000',
            'is_active' => true,
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('contacts', [
            'name' => 'Joao Silva',
            'type' => 'citizen',
            'organization_id' => $admin->organization_id,
        ]);
    }

    public function test_contact_creation_validates_required_fields(): void
    {
        $admin = $this->makeAdminWithPermissions(['contacts.create']);

        $response = $this->actingAs($admin)->post(route('admin.contacts.store'), [
            'email' => 'invalid',
        ]);

        $response->assertSessionHasErrors(['type', 'name']);
    }
}
