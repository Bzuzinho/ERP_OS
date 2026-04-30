<?php

namespace App\Policies;

use App\Models\InventoryMovement;
use App\Models\User;

class InventoryMovementPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->hasRole('super_admin') ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->can('inventory.view');
    }

    public function view(User $user, InventoryMovement $inventoryMovement): bool
    {
        return $user->can('inventory.view');
    }

    public function create(User $user): bool
    {
        return $user->can('inventory.move') || $user->can('inventory.adjust');
    }

    public function update(User $user, InventoryMovement $inventoryMovement): bool
    {
        return $user->can('inventory.adjust');
    }

    public function delete(User $user, InventoryMovement $inventoryMovement): bool
    {
        return $user->can('inventory.delete');
    }
}
