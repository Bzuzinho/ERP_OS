<?php

namespace Tests\Feature\Mobile;

use App\Models\Attachment;
use App\Models\Organization;
use App\Models\Task;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Concerns\BuildsUsersWithPermissions;
use Tests\TestCase;

class MobileTaskAttachmentTest extends TestCase
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

        Storage::fake('public');
    }

    public function test_operational_user_can_upload_photo_to_task(): void
    {
        $user = $this->makeAdminWithPermissions(['tasks.view', 'tasks.complete']);

        $task = Task::factory()->create([
            'organization_id' => $user->organization_id,
            'assigned_to' => $user->id,
            'status' => 'in_progress',
        ]);

        $file = UploadedFile::fake()->image('task-photo.jpg', 800, 600);

        $this->actingAs($user)
            ->post(route('mobile.tasks.attachments.store', $task), [
                'file' => $file,
            ])
            ->assertRedirect();

        // Verify attachment was created
        $this->assertDatabaseHas('attachments', [
            'attachable_type' => Task::class,
            'attachable_id' => $task->id,
            'uploaded_by' => $user->id,
            'organization_id' => $user->organization_id,
        ]);
    }

    public function test_attachment_has_correct_organization_and_uploader(): void
    {
        $user = $this->makeAdminWithPermissions(['tasks.view', 'tasks.complete']);

        $task = Task::factory()->create([
            'organization_id' => $user->organization_id,
            'assigned_to' => $user->id,
            'status' => 'in_progress',
        ]);

        $file = UploadedFile::fake()->create('document.pdf', 512, 'application/pdf');

        $this->actingAs($user)
            ->post(route('mobile.tasks.attachments.store', $task), [
                'file' => $file,
            ])
            ->assertRedirect();

        $attachment = Attachment::query()
            ->where('attachable_id', $task->id)
            ->where('attachable_type', Task::class)
            ->first();

        $this->assertNotNull($attachment);
        $this->assertEquals($user->organization_id, $attachment->organization_id);
        $this->assertEquals($user->id, $attachment->uploaded_by);
    }

    public function test_user_without_permission_cannot_upload_to_task(): void
    {
        $user = $this->makePortalUser('cidadao');
        $user->givePermissionTo('tasks.view');

        $task = Task::factory()->create([
            'organization_id' => $user->organization_id,
            'assigned_to' => $user->id,
            'status' => 'in_progress',
        ]);

        $file = UploadedFile::fake()->image('photo.jpg');

        $this->actingAs($user)
            ->post(route('mobile.tasks.attachments.store', $task), [
                'file' => $file,
            ])
            ->assertForbidden();
    }

    public function test_user_cannot_upload_to_task_from_another_organization(): void
    {
        $user = $this->makeAdminWithPermissions(['tasks.view', 'tasks.complete']);
        $otherOrg = Organization::factory()->create();
        $otherOrgUser = $this->makeAdminWithPermissions(['tasks.view', 'tasks.complete'], $otherOrg);

        $task = Task::factory()->create([
            'organization_id' => $otherOrg->id,
            'assigned_to' => $otherOrgUser->id,
            'status' => 'in_progress',
        ]);

        $file = UploadedFile::fake()->image('photo.jpg');

        $response = $this->actingAs($user)
            ->post(route('mobile.tasks.attachments.store', $task), [
                'file' => $file,
            ]);

        $this->assertContains($response->status(), [403, 404]);
    }

    public function test_rejected_file_types_cannot_be_uploaded(): void
    {
        $user = $this->makeAdminWithPermissions(['tasks.view', 'tasks.complete']);

        $task = Task::factory()->create([
            'organization_id' => $user->organization_id,
            'assigned_to' => $user->id,
            'status' => 'in_progress',
        ]);

        // Use a valid fake file with a disallowed extension by renaming
        $file = UploadedFile::fake()->create('script.php', 100, 'text/plain');

        $this->actingAs($user)
            ->post(route('mobile.tasks.attachments.store', $task), [
                'file' => $file,
            ])
            ->assertSessionHasErrors('file');
    }
}
