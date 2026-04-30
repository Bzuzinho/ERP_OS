<?php

namespace App\Policies;

use App\Models\InventoryLocation;
use App\Models\User;

class InventoryLocationPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->hasRole('super_admin') ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->can('inventory.view');
    }

    public function view(User $user, InventoryLocation $inventoryLocation): bool
    {
        return $user->can('inventory.view');
    }

    public function create(User $user): bool
    {
        return $user->can('inventory.manage_locations') || $user->can('inventory.create');
    }

    public function update(User $user, InventoryLocation $inventoryLocation): bool
    {
        return $user->can('inventory.manage_locations') || $user->can('inventory.update');
    }

    public function delete(User $user, InventoryLocation $inventoryLocation): bool
    {
        return $user->can('inventory.manage_locations') || $user->can('inventory.delete');
    }
}
