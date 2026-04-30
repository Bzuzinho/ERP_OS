<?php

namespace App\Actions\Inventory;

use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use App\Models\User;

class RegisterInventoryExitAction
{
    public function __construct(private readonly RegisterInventoryMovementAction $registerMovementAction)
    {
    }

    public function execute(InventoryItem $item, User $performedBy, array $data): InventoryMovement
    {
        return $this->registerMovementAction->execute($item, $performedBy, [
            ...$data,
            'movement_type' => $data['movement_type'] ?? 'exit',
        ]);
    }
}
