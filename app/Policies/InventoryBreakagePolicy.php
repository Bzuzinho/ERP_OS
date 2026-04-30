<?php

namespace App\Policies;

use App\Models\InventoryBreakage;
use App\Models\User;

class InventoryBreakagePolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->hasRole('super_admin') ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->can('inventory.view') || $user->can('inventory.breakage');
    }

    public function view(User $user, InventoryBreakage $inventoryBreakage): bool
    {
        return $user->can('inventory.view') || $user->can('inventory.breakage');
    }

    public function create(User $user): bool
    {
        return $user->can('inventory.breakage');
    }

    public function update(User $user, InventoryBreakage $inventoryBreakage): bool
    {
        return $user->can('inventory.breakage');
    }

    public function delete(User $user, InventoryBreakage $inventoryBreakage): bool
    {
        return $user->can('inventory.delete');
    }

    public function resolve(User $user, InventoryBreakage $inventoryBreakage): bool
    {
        return $user->can('inventory.breakage');
    }
}
