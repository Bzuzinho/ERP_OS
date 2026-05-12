<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'organization_id',
    'requested_by',
    'approved_by',
    'rejected_by',
    'status',
    'requestable_type',
    'requestable_id',
    'title',
    'purpose',
    'needed_from',
    'needed_until',
    'approved_at',
    'rejected_at',
    'rejection_reason',
    'prepared_at',
    'delivered_at',
    'returned_at',
    'notes',
])]
class ResourceRequest extends Model
{
    /** @use HasFactory<\Database\Factories\ResourceRequestFactory> */
    use BelongsToOrganization, HasFactory, SoftDeletes;

    public const STATUSES = ['requested', 'approved', 'rejected', 'prepared', 'delivered', 'partially_returned', 'returned', 'cancelled'];

    protected function casts(): array
    {
        return [
            'needed_from' => 'datetime',
            'needed_until' => 'datetime',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
            'prepared_at' => 'datetime',
            'delivered_at' => 'datetime',
            'returned_at' => 'datetime',
        ];
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function requestable(): MorphTo
    {
        return $this->morphTo();
    }

    public function items(): HasMany
    {
        return $this->hasMany(ResourceRequestItem::class);
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
