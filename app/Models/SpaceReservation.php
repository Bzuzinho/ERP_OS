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
    'space_id',
    'requested_by_user_id',
    'contact_id',
    'event_id',
    'status',
    'start_at',
    'end_at',
    'purpose',
    'notes',
    'internal_notes',
    'approved_by',
    'approved_at',
    'rejected_by',
    'rejected_at',
    'rejection_reason',
    'cancelled_by',
    'cancelled_at',
    'cancellation_reason',
])]
class SpaceReservation extends Model
{
    /** @use HasFactory<\Database\Factories\SpaceReservationFactory> */
    use HasFactory, SoftDeletes;

    public const STATUSES = ['requested', 'approved', 'rejected', 'cancelled', 'completed'];

    protected function casts(): array
    {
        return [
            'start_at' => 'datetime',
            'end_at' => 'datetime',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function space(): BelongsTo
    {
        return $this->belongsTo(Space::class);
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by_user_id');
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(SpaceReservationApproval::class);
    }

    public function cleaningRecords(): HasMany
    {
        return $this->hasMany(SpaceCleaningRecord::class);
    }

    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class, 'related_space_reservation_id');
    }

    public function inventoryLoans(): HasMany
    {
        return $this->hasMany(InventoryLoan::class, 'related_space_reservation_id');
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'related', 'related_type', 'related_id');
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
