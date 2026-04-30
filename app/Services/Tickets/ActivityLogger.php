<?php

namespace App\Services\Tickets;

use App\Models\ActivityLog;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ActivityLogger
{
    public function log(
        Model $subject,
        string $action,
        ?User $user = null,
        ?Organization $organization = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $description = null,
    ): ActivityLog {
        return ActivityLog::create([
            'organization_id' => $organization?->id ?? $user?->organization_id,
            'user_id' => $user?->id,
            'subject_type' => $subject::class,
            'subject_id' => $subject->getKey(),
            'action' => $action,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'description' => $description,
        ]);
    }
}
