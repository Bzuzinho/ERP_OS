<?php

namespace App\Policies;

use App\Models\InventoryLoan;
use App\Models\User;

class InventoryLoanPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->hasRole('super_admin') ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->can('inventory.view') || $user->can('inventory.loan');
    }

    public function view(User $user, InventoryLoan $inventoryLoan): bool
    {
        return $user->can('inventory.view') || $user->can('inventory.loan');
    }

    public function create(User $user): bool
    {
        return $user->can('inventory.loan');
    }

    public function update(User $user, InventoryLoan $inventoryLoan): bool
    {
        return $user->can('inventory.loan');
    }

    public function delete(User $user, InventoryLoan $inventoryLoan): bool
    {
        return $user->can('inventory.delete');
    }

    public function return(User $user, InventoryLoan $inventoryLoan): bool
    {
        return $user->can('inventory.return') || $user->can('inventory.loan');
    }
}
