<?php

namespace Tests\Feature\Sprint2;

use App\Models\Contact;
use App\Models\Event;
use App\Models\EventParticipant;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\BuildsUsersWithPermissions;
use Tests\TestCase;

class PortalEventsVisibilityFeatureTest extends TestCase
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

    public function test_portal_user_sees_public_events_in_index(): void
    {
        $user = $this->makePortalUser('cidadao');

        Event::factory()->create([
            'organization_id' => $user->organization_id,
            'visibility' => 'public',
        ]);

        $response = $this->actingAs($user)->get(route('portal.events.index'));

        $response->assertOk();
    }

    public function test_portal_user_sees_restricted_event_when_related_contact_belongs_to_user(): void
    {
        $user = $this->makePortalUser('cidadao');
        $contact = Contact::factory()->forUser($user)->create();

        $event = Event::factory()->create([
            'organization_id' => $user->organization_id,
            'visibility' => 'restricted',
            'related_contact_id' => $contact->id,
        ]);

        $response = $this->actingAs($user)->get(route('portal.events.show', $event));

        $response->assertOk();
    }

    public function test_portal_user_sees_restricted_event_when_invited_as_participant(): void
    {
        $user = $this->makePortalUser('cidadao');

        $event = Event::factory()->create([
            'organization_id' => $user->organization_id,
            'visibility' => 'restricted',
        ]);

        EventParticipant::factory()->create([
            'event_id' => $event->id,
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->get(route('portal.events.show', $event));

        $response->assertOk();
    }

    public function test_portal_user_cannot_view_internal_event_even_when_related_contact_matches(): void
    {
        $user = $this->makePortalUser('cidadao');
        $contact = Contact::factory()->forUser($user)->create();

        $event = Event::factory()->create([
            'organization_id' => $user->organization_id,
            'visibility' => 'internal',
            'related_contact_id' => $contact->id,
        ]);

        $response = $this->actingAs($user)->get(route('portal.events.show', $event));

        $response->assertForbidden();
    }
}
