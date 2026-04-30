<?php

namespace App\Actions\Inventory;

use App\Models\InventoryBreakage;
use App\Models\User;
use App\Services\Tickets\ActivityLogger;

class ResolveInventoryBreakageAction
{
    public function __construct(private readonly ActivityLogger $activityLogger)
    {
    }

    public function execute(InventoryBreakage $breakage, User $performedBy, string $status, ?string $resolutionNotes = null): InventoryBreakage
    {
        $oldStatus = $breakage->status;

        $breakage->status = $status;
        $breakage->resolution_notes = $resolutionNotes;

        if (in_array($status, ['resolved', 'written_off'], true)) {
            $breakage->resolved_by = $performedBy->id;
            $breakage->resolved_at = now();
        }

        $breakage->save();

        $this->activityLogger->log(
            subject: $breakage,
            action: 'inventory.breakage.resolved',
            user: $performedBy,
            organization: $breakage->organization,
            oldValues: ['status' => $oldStatus],
            newValues: ['status' => $status],
            description: 'Quebra de inventario resolvida.',
        );

        return $breakage;
    }
}
