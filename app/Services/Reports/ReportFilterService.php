<?php

namespace App\Services\Reports;

use Illuminate\Database\Eloquent\Builder;

class ReportFilterService
{
    public function normalize(array $filters): array
    {
        return [
            'date_from' => $filters['date_from'] ?? null,
            'date_to' => $filters['date_to'] ?? null,
            'status' => $filters['status'] ?? null,
            'priority' => $filters['priority'] ?? null,
            'category' => $filters['category'] ?? null,
            'department_id' => $filters['department_id'] ?? null,
            'user_id' => $filters['user_id'] ?? null,
            'employee_id' => $filters['employee_id'] ?? null,
            'contact_id' => $filters['contact_id'] ?? null,
            'space_id' => $filters['space_id'] ?? null,
            'inventory_item_id' => $filters['inventory_item_id'] ?? null,
            'plan_type' => $filters['plan_type'] ?? null,
            'search' => $filters['search'] ?? null,
            'source' => $filters['source'] ?? null,
            'assigned_to' => $filters['assigned_to'] ?? null,
        ];
    }

    public function applyDateRange(Builder $query, array $filters, string $column = 'created_at'): Builder
    {
        return $query
            ->when($filters['date_from'] ?? null, fn (Builder $q, $value) => $q->whereDate($column, '>=', $value))
            ->when($filters['date_to'] ?? null, fn (Builder $q, $value) => $q->whereDate($column, '<=', $value));
    }
}
