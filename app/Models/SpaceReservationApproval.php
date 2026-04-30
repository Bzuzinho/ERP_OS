<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'space_reservation_id',
    'action',
    'decided_by',
    'notes',
    'old_status',
    'new_status',
])]
class SpaceReservationApproval extends Model
{
    /** @use HasFactory<\Database\Factories\SpaceReservationApprovalFactory> */
    use HasFactory;

    public const ACTIONS = ['requested', 'approved', 'rejected', 'cancelled', 'completed'];

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(SpaceReservation::class, 'space_reservation_id');
    }

    public function decidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by');
    }
}
