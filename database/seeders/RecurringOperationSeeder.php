<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Organization;
use App\Models\RecurringOperation;
use App\Models\Space;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;

class RecurringOperationSeeder extends Seeder
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

        RecurringOperation::query()->firstOrCreate(
            ['organization_id' => $organization->id, 'title' => 'Limpeza semanal da Sala Polivalente'],
            [
                'description' => 'Recorrência demo de limpeza semanal.',
                'operation_type' => 'cleaning',
                'status' => 'active',
                'frequency' => 'weekly',
                'interval' => 1,
                'weekdays' => ['monday'],
                'start_date' => now()->toDateString(),
                'next_run_at' => now()->addDays(1),
                'owner_user_id' => $creator->id,
                'department_id' => $department?->id,
                'team_id' => $team?->id,
                'related_space_id' => $space?->id,
                'task_template' => ['title' => 'Executar limpeza da Sala Polivalente', 'priority' => 'normal'],
                'created_by' => $creator->id,
            ],
        );

        RecurringOperation::query()->firstOrCreate(
            ['organization_id' => $organization->id, 'title' => 'Inspeção mensal ao Parque Infantil'],
            [
                'description' => 'Recorrência demo de inspeção mensal.',
                'operation_type' => 'inspection',
                'status' => 'active',
                'frequency' => 'monthly',
                'interval' => 1,
                'day_of_month' => 10,
                'start_date' => now()->toDateString(),
                'next_run_at' => now()->addDays(5),
                'owner_user_id' => $creator->id,
                'department_id' => $department?->id,
                'team_id' => $team?->id,
                'related_space_id' => $space?->id,
                'task_template' => ['title' => 'Inspecionar parque infantil', 'priority' => 'high'],
                'created_by' => $creator->id,
            ],
        );
    }
}
