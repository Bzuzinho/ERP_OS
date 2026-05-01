<?php

namespace App\Services\Dashboard;

use App\Models\InventoryBreakage;
use App\Models\InventoryItem;
use App\Models\InventoryLoan;
use App\Models\InventoryMovement;
use App\Models\InventoryRestockRequest;
use App\Models\User;
use App\Services\Reports\ReportFilterService;

class InventoryKpiService
{
    public function __construct(private readonly ReportFilterService $filters)
    {
    }

    public function getSummary(array $filters, User $user): array
    {
        $normalized = $this->filters->normalize($filters);

        $items = InventoryItem::query()->where('organization_id', $user->organization_id)
            ->when($normalized['inventory_item_id'], fn ($q, $value) => $q->whereKey($value));

        $movements = InventoryMovement::query()->where('organization_id', $user->organization_id);
        $this->filters->applyDateRange($movements, $normalized, 'occurred_at');

        $loans = InventoryLoan::query()->where('organization_id', $user->organization_id);
        $this->filters->applyDateRange($loans, $normalized, 'loaned_at');

        return [
            'total_items' => (clone $items)->count(),
            'active_items' => (clone $items)->where('is_active', true)->count(),
            'low_stock' => (clone $items)->whereNotNull('minimum_stock')->whereColumn('current_stock', '<', 'minimum_stock')->count(),
            'out_of_stock' => (clone $items)->where('current_stock', '<=', 0)->count(),
            'movements_in_period' => (clone $movements)->count(),
            'active_loans' => (clone $loans)->whereIn('status', ['active', 'overdue'])->count(),
            'overdue_loans' => (clone $loans)->where(function ($query) {
                $query->where('status', 'overdue')
                    ->orWhere(function ($sub) {
                        $sub->where('status', 'active')
                            ->whereNotNull('expected_return_at')
                            ->where('expected_return_at', '<', now());
                    });
            })->count(),
            'pending_restock' => InventoryRestockRequest::query()->where('organization_id', $user->organization_id)->where('status', 'requested')->count(),
            'reported_breakages' => InventoryBreakage::query()->where('organization_id', $user->organization_id)->where('status', 'reported')->count(),
        ];
    }
}
