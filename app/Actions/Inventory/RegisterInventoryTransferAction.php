<?php

namespace App\Actions\Inventory;

use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use App\Models\User;
use RuntimeException;

class RegisterInventoryTransferAction
{
    public function __construct(private readonly RegisterInventoryMovementAction $registerMovementAction)
    {
    }

    public function execute(InventoryItem $item, User $performedBy, array $data): InventoryMovement
    {
        if (empty($data['from_location_id']) || empty($data['to_location_id'])) {
            throw new RuntimeException('Transferencia exige local de origem e destino.');
        }

        return $this->registerMovementAction->execute($item, $performedBy, [
            ...$data,
            'movement_type' => 'transfer',
        ]);
    }
}
