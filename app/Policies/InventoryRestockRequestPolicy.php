<?php

namespace App\Policies;

use App\Models\InventoryRestockRequest;
use App\Models\User;

class InventoryRestockRequestPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->hasRole('super_admin') ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->can('inventory.view') || $user->can('inventory.restock');
    }

    public function view(User $user, InventoryRestockRequest $inventoryRestockRequest): bool
    {
        return $user->can('inventory.view') || $user->can('inventory.restock');
    }

    public function create(User $user): bool
    {
        return $user->can('inventory.restock');
    }

    public function update(User $user, InventoryRestockRequest $inventoryRestockRequest): bool
    {
        return $user->can('inventory.restock');
    }

    public function delete(User $user, InventoryRestockRequest $inventoryRestockRequest): bool
    {
        return $user->can('inventory.delete');
    }

    public function approve(User $user, InventoryRestockRequest $inventoryRestockRequest): bool
    {
        return $user->can('inventory.approve_restock');
    }

    public function reject(User $user, InventoryRestockRequest $inventoryRestockRequest): bool
    {
        return $user->can('inventory.approve_restock');
    }

    public function complete(User $user, InventoryRestockRequest $inventoryRestockRequest): bool
    {
        return $user->can('inventory.restock') || $user->can('inventory.approve_restock');
    }
}
