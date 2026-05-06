<?php

namespace App\Policies;

use App\Models\InventoryItem;
use App\Models\User;
use App\Support\OrganizationScope;

class InventoryItemPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->hasRole('super_admin') ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->can('inventory.view');
    }

    public function view(User $user, InventoryItem $inventoryItem): bool
    {
        return $user->can('inventory.view')
            && OrganizationScope::sameOrganization($inventoryItem->organization_id, $user);
    }

    public function create(User $user): bool
    {
        return $user->can('inventory.create');
    }

    public function update(User $user, InventoryItem $inventoryItem): bool
    {
        return $user->can('inventory.update')
            && OrganizationScope::sameOrganization($inventoryItem->organization_id, $user);
    }

    public function delete(User $user, InventoryItem $inventoryItem): bool
    {
        return $user->can('inventory.delete')
            && OrganizationScope::sameOrganization($inventoryItem->organization_id, $user);
    }
}
