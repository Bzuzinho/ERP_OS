<?php

namespace App\Actions\Hr;

use App\Models\ActivityLog;
use App\Models\Team;
use Illuminate\Support\Str;

class CreateTeamAction
{
    public function execute(array $data): Team
    {
        $organizationId = $data['organization_id'] ?? auth()->user()->organization_id;

        // Generate slug if not provided
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $team = Team::create([
            ...$data,
            'organization_id' => $organizationId,
        ]);

        ActivityLog::create([
            'organization_id' => $organizationId,
            'user_id' => auth()->id(),
            'action' => 'created',
            'subject_type' => Team::class,
            'subject_id' => $team->id,
            'description' => "Equipa '{$team->name}' criada",
        ]);

        return $team;
    }
}
