<?php

namespace App\Actions\Inventory;

use App\Models\InventoryItem;
use App\Models\User;
use App\Services\Tickets\ActivityLogger;

class UpdateInventoryItemStatusAction
{
    public function __construct(private readonly ActivityLogger $activityLogger)
    {
    }

    public function execute(InventoryItem $item, string $status, User $performedBy): InventoryItem
    {
        $oldStatus = $item->status;

        if ($oldStatus === $status) {
            return $item;
        }

        $item->status = $status;
        $item->save();

        $this->activityLogger->log(
            subject: $item,
            action: 'inventory.item.status_updated',
            user: $performedBy,
            organization: $item->organization,
            oldValues: ['status' => $oldStatus],
            newValues: ['status' => $status],
            description: 'Estado do item de inventario atualizado.',
        );

        return $item;
    }
}
