<?php

namespace App\Policies;

use App\Models\InventoryCategory;
use App\Models\User;

class InventoryCategoryPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->hasRole('super_admin') ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->can('inventory.view');
    }

    public function view(User $user, InventoryCategory $inventoryCategory): bool
    {
        return $user->can('inventory.view');
    }

    public function create(User $user): bool
    {
        return $user->can('inventory.manage_categories') || $user->can('inventory.create');
    }

    public function update(User $user, InventoryCategory $inventoryCategory): bool
    {
        return $user->can('inventory.manage_categories') || $user->can('inventory.update');
    }

    public function delete(User $user, InventoryCategory $inventoryCategory): bool
    {
        return $user->can('inventory.manage_categories') || $user->can('inventory.delete');
    }
}
