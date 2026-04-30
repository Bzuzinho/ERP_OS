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
    'title',
    'description',
    'event_type',
    'status',
    'start_at',
    'end_at',
    'location_text',
    'created_by',
    'related_ticket_id',
    'related_contact_id',
    'visibility',
])]
class Event extends Model
{
    use HasFactory, SoftDeletes;

    public const TYPES = ['meeting', 'appointment', 'visit', 'activity', 'maintenance', 'assembly', 'reservation'];

    public const STATUSES = ['scheduled', 'confirmed', 'cancelled', 'completed'];

    public const VISIBILITIES = ['public', 'internal', 'restricted'];

    public const ATTENDANCE_STATUSES = ['invited', 'confirmed', 'declined', 'attended', 'absent'];

    protected function casts(): array
    {
        return [
            'start_at' => 'datetime',
            'end_at' => 'datetime',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function relatedTicket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class, 'related_ticket_id');
    }

    public function relatedContact(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'related_contact_id');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(EventParticipant::class);
    }

    public function meetingMinutes(): HasMany
    {
        return $this->hasMany(MeetingMinute::class);
    }

    public function spaceReservations(): HasMany
    {
        return $this->hasMany(SpaceReservation::class);
    }

    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class, 'related_event_id');
    }

    public function inventoryLoans(): HasMany
    {
        return $this->hasMany(InventoryLoan::class, 'related_event_id');
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
}
