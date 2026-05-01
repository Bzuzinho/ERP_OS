<?php

namespace App\Services\Reports;

use App\Models\InventoryItem;
use App\Models\User;
use App\Services\Dashboard\InventoryKpiService;
use App\Services\Inventory\InventoryStockService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class InventoryReportService
{
    public function __construct(
        private readonly ReportFilterService $filters,
        private readonly InventoryKpiService $kpiService,
        private readonly InventoryStockService $stockService,
    ) {
    }

    public function getSummary(array $filters, User $user): array
    {
        return $this->kpiService->getSummary($filters, $user);
    }

    public function getRows(array $filters, User $user): LengthAwarePaginator
    {
        $normalized = $this->filters->normalize($filters);

        $query = InventoryItem::query()
            ->where('organization_id', $user->organization_id)
            ->with(['category:id,name', 'location:id,name'])
            ->when($normalized['status'], fn ($q, $value) => $q->where('status', $value))
            ->when($normalized['category'], fn ($q, $value) => $q->whereHas('category', fn ($categoryQuery) => $categoryQuery->where('name', $value)))
            ->when($normalized['inventory_item_id'], fn ($q, $value) => $q->whereKey($value))
            ->when($normalized['search'], fn ($q, $value) => $q->where(function ($sub) use ($value) {
                $sub->where('name', 'like', "%{$value}%")
                    ->orWhere('sku', 'like', "%{$value}%");
            }))
            ->latest();

        $this->filters->applyDateRange($query, $normalized);

        return $query->paginate(15)->withQueryString();
    }

    public function exportRows(array $filters, User $user): Collection
    {
        $normalized = $this->filters->normalize($filters);

        $query = InventoryItem::query()
            ->where('organization_id', $user->organization_id)
            ->with(['category:id,name', 'location:id,name'])
            ->when($normalized['status'], fn ($q, $value) => $q->where('status', $value))
            ->when($normalized['category'], fn ($q, $value) => $q->whereHas('category', fn ($categoryQuery) => $categoryQuery->where('name', $value)))
            ->when($normalized['inventory_item_id'], fn ($q, $value) => $q->whereKey($value))
            ->when($normalized['search'], fn ($q, $value) => $q->where(function ($sub) use ($value) {
                $sub->where('name', 'like', "%{$value}%")
                    ->orWhere('sku', 'like', "%{$value}%");
            }))
            ->latest();

        $this->filters->applyDateRange($query, $normalized);

        return $query->limit(5000)->get()->map(function (InventoryItem $item) {
            return [
                'item' => $item->name,
                'categoria' => $item->category?->name,
                'localizacao' => $item->location?->name,
                'stock_atual' => (float) $item->current_stock,
                'stock_minimo' => $item->minimum_stock !== null ? (float) $item->minimum_stock : null,
                'estado_stock' => $this->stockService->getStockStatus($item),
                'tipo' => $item->item_type,
                'emprestavel' => $item->is_loanable ? 'sim' : 'nao',
                'status' => $item->status,
            ];
        });
    }
}
