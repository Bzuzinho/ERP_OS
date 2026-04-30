<?php

namespace App\Services\Inventory;

use App\Models\InventoryLoan;
use Illuminate\Database\Eloquent\Builder;

class InventoryLoanService
{
    public function markOverdueLoans(): int
    {
        return InventoryLoan::query()
            ->where('status', 'active')
            ->whereNotNull('expected_return_at')
            ->where('expected_return_at', '<', now())
            ->update(['status' => 'overdue']);
    }

    public function activeLoansQuery(): Builder
    {
        return InventoryLoan::query()->whereIn('status', ['active', 'overdue']);
    }

    public function overdueLoansQuery(): Builder
    {
        return InventoryLoan::query()
            ->where(function (Builder $builder) {
                $builder->where('status', 'overdue')
                    ->orWhere(function (Builder $activeBuilder) {
                        $activeBuilder->where('status', 'active')
                            ->whereNotNull('expected_return_at')
                            ->where('expected_return_at', '<', now());
                    });
            });
    }
}
