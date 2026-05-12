<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'resource_request_id',
    'inventory_item_id',
    'quantity_requested',
    'quantity_approved',
    'quantity_delivered',
    'quantity_returned',
    'notes',
])]
class ResourceRequestItem extends Model
{
    /** @use HasFactory<\Database\Factories\ResourceRequestItemFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'quantity_requested' => 'decimal:2',
            'quantity_approved' => 'decimal:2',
            'quantity_delivered' => 'decimal:2',
            'quantity_returned' => 'decimal:2',
        ];
    }

    public function resourceRequest(): BelongsTo
    {
        return $this->belongsTo(ResourceRequest::class);
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }

    public function inventoryLoans(): HasMany
    {
        return $this->hasMany(InventoryLoan::class);
    }

    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }
}
