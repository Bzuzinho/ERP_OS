<?php

namespace Tests\Feature\SpaceReservations;

use App\Models\Organization;
use App\Models\Space;
use App\Models\SpaceReservation;
use App\Models\Task;
use App\Models\User;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\BuildsUsersWithPermissions;
use Tests\TestCase;

class SpaceReservationTaskGenerationTest extends TestCase
{
    use BuildsUsersWithPermissions;
    use RefreshDatabase;

    private Organization $organization;

    private User $approver;

    private Space $space;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            OrganizationSeeder::class,
            RoleAndPermissionSeeder::class,
        ]);

        $this->organization = Organization::factory()->create();
        $this->approver = $this->makeAdminWithPermissions([
            'spaces.view',
            'spaces.approve_reservation',
            'events.view',
            'events.create',
            'tasks.view',
            'tasks.create',
            'tasks.update',
            'tasks.submit-validation',
        ], $this->organization);

        $this->space = Space::factory()->create([
            'organization_id' => $this->organization->id,
            'requires_approval' => true,
            'has_cleaning_required' => true,
        ]);
    }

    public function test_approval_creates_preparation_and_cleaning_tasks_with_scope(): void
    {
        $reservation = SpaceReservation::factory()->create([
            'organization_id' => $this->organization->id,
            'space_id' => $this->space->id,
            'requested_by_user_id' => $this->approver->id,
            'status' => 'requested',
            'start_at' => now()->addDays(1)->setHour(8)->setMinute(0),
            'end_at' => now()->addDays(1)->setHour(10)->setMinute(0),
            'purpose' => 'Atividade desportiva',
        ]);

        $this->actingAs($this->approver)->post(route('admin.space-reservations.approve', $reservation));

        $tasks = Task::query()
            ->where('space_reservation_id', $reservation->id)
            ->orderBy('id')
            ->get();

        $this->assertCount(2, $tasks);
        $this->assertTrue($tasks->pluck('title')->contains(fn (string $title) => str_contains($title, 'Preparar espaco')));
        $this->assertTrue($tasks->pluck('title')->contains(fn (string $title) => str_contains($title, 'Limpeza apos reserva')));

        foreach ($tasks as $task) {
            $this->assertEquals($this->organization->id, $task->organization_id);
            $this->assertEquals('pending', $task->status);
            $this->assertContains($task->status, Task::STATUSES);
            $this->assertEquals($reservation->id, $task->space_reservation_id);
        }
    }

    public function test_task_generation_is_idempotent_for_same_reservation(): void
    {
        $reservation = SpaceReservation::factory()->create([
            'organization_id' => $this->organization->id,
            'space_id' => $this->space->id,
            'requested_by_user_id' => $this->approver->id,
            'status' => 'requested',
            'start_at' => now()->addDays(2)->setHour(8)->setMinute(0),
            'end_at' => now()->addDays(2)->setHour(11)->setMinute(0),
        ]);

        $this->actingAs($this->approver)->post(route('admin.space-reservations.approve', $reservation));
        $this->actingAs($this->approver)->post(route('admin.space-reservations.approve', $reservation));

        $this->assertSame(2, Task::query()->where('space_reservation_id', $reservation->id)->count());
    }
}
