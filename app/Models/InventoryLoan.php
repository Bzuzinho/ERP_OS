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
    'borrower_user_id',
    'borrower_contact_id',
    'quantity',
    'loaned_at',
    'expected_return_at',
    'returned_at',
    'status',
    'loaned_by',
    'returned_to',
    'related_ticket_id',
    'related_task_id',
    'related_event_id',
    'related_space_reservation_id',
    'notes',
    'return_notes',
])]
class InventoryLoan extends Model
{
    /** @use HasFactory<\Database\Factories\InventoryLoanFactory> */
    use HasFactory, SoftDeletes;

    public const STATUSES = ['active', 'returned', 'overdue', 'lost', 'damaged', 'cancelled'];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'loaned_at' => 'datetime',
            'expected_return_at' => 'datetime',
            'returned_at' => 'datetime',
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

    public function borrowerUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'borrower_user_id');
    }

    public function borrowerContact(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'borrower_contact_id');
    }

    public function loanedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'loaned_by');
    }

    public function returnedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'returned_to');
    }

    public function relatedTicket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class, 'related_ticket_id');
    }

    public function relatedTask(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'related_task_id');
    }

    public function relatedEvent(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'related_event_id');
    }

    public function relatedSpaceReservation(): BelongsTo
    {
        return $this->belongsTo(SpaceReservation::class, 'related_space_reservation_id');
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
