<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'organization_id',
    'inventory_item_id',
    'requested_by',
    'approved_by',
    'quantity_requested',
    'quantity_approved',
    'status',
    'reason',
    'approved_at',
    'rejected_at',
    'rejection_reason',
    'completed_at',
    'notes',
])]
class InventoryRestockRequest extends Model
{
    /** @use HasFactory<\Database\Factories\InventoryRestockRequestFactory> */
    use HasFactory, SoftDeletes;

    public const STATUSES = ['requested', 'approved', 'rejected', 'completed', 'cancelled'];

    protected function casts(): array
    {
        return [
            'quantity_requested' => 'decimal:2',
            'quantity_approved' => 'decimal:2',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id');
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function activityLogs(): MorphMany
    {
        return $this->morphMany(ActivityLog::class, 'subject');
    }
}
