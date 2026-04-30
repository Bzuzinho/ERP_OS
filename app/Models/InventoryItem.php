<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'organization_id',
    'inventory_category_id',
    'inventory_location_id',
    'name',
    'slug',
    'description',
    'sku',
    'item_type',
    'unit',
    'current_stock',
    'minimum_stock',
    'maximum_stock',
    'unit_cost',
    'status',
    'is_stock_tracked',
    'is_loanable',
    'is_active',
])]
class InventoryItem extends Model
{
    /** @use HasFactory<\Database\Factories\InventoryItemFactory> */
    use HasFactory, SoftDeletes;

    public const ITEM_TYPES = ['consumable', 'equipment', 'vehicle', 'tool', 'furniture', 'document', 'other'];

    public const UNITS = ['unit', 'box', 'pack', 'liter', 'kg', 'meter', 'hour', 'day', 'other'];

    public const STATUSES = ['active', 'inactive', 'damaged', 'lost', 'maintenance', 'retired'];

    protected function casts(): array
    {
        return [
            'current_stock' => 'decimal:2',
            'minimum_stock' => 'decimal:2',
            'maximum_stock' => 'decimal:2',
            'unit_cost' => 'decimal:2',
            'is_stock_tracked' => 'boolean',
            'is_loanable' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(InventoryCategory::class, 'inventory_category_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(InventoryLocation::class, 'inventory_location_id');
    }

    public function movements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }

    public function loans(): HasMany
    {
        return $this->hasMany(InventoryLoan::class);
    }

    public function restockRequests(): HasMany
    {
        return $this->hasMany(InventoryRestockRequest::class);
    }

    public function breakages(): HasMany
    {
        return $this->hasMany(InventoryBreakage::class);
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'related', 'related_type', 'related_id');
    }

    public function activityLogs(): MorphMany
    {
        return $this->morphMany(ActivityLog::class, 'subject');
    }
}
