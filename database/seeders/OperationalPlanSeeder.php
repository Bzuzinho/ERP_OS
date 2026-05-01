<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\OperationalPlan;
use App\Models\Organization;
use App\Models\Space;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;

class OperationalPlanSeeder extends Seeder
{
    public function run(): void
    {
        $organization = Organization::query()->first();
        $creator = User::query()->where('organization_id', $organization?->id)->first();

        if (! $organization || ! $creator) {
            return;
        }

        $department = Department::query()->where('organization_id', $organization->id)->first();
        $team = Team::query()->where('organization_id', $organization->id)->first();
        $space = Space::query()->where('organization_id', $organization->id)->first();

        $plans = [
            ['title' => 'Limpeza semanal de espaços públicos', 'plan_type' => 'cleaning', 'visibility' => 'internal'],
            ['title' => 'Manutenção preventiva de equipamentos', 'plan_type' => 'maintenance', 'visibility' => 'internal'],
            ['title' => 'Campanha de sensibilização ambiental', 'plan_type' => 'campaign', 'visibility' => 'portal'],
            ['title' => 'Evento público da freguesia', 'plan_type' => 'public_event', 'visibility' => 'public'],
        ];

        foreach ($plans as $index => $data) {
            OperationalPlan::query()->firstOrCreate(
                ['organization_id' => $organization->id, 'slug' => 's7-plan-'.($index + 1)],
                [
                    'title' => $data['title'],
                    'description' => $data['title'].' (demo)',
                    'plan_type' => $data['plan_type'],
                    'status' => 'approved',
                    'visibility' => $data['visibility'],
                    'start_date' => now()->addDays($index)->toDateString(),
                    'end_date' => now()->addDays($index + 15)->toDateString(),
                    'owner_user_id' => $creator->id,
                    'department_id' => $department?->id,
                    'team_id' => $team?->id,
                    'related_space_id' => $space?->id,
                    'progress_percent' => 20 * ($index + 1),
                    'approved_by' => $creator->id,
                    'approved_at' => now(),
                    'created_by' => $creator->id,
                ],
            );
        }
    }
}
