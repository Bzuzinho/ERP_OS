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
    'name',
    'slug',
    'description',
    'location_text',
    'capacity',
    'status',
    'requires_approval',
    'has_cleaning_required',
    'has_deposit',
    'deposit_amount',
    'price',
    'rules',
    'is_public',
    'is_active',
])]
class Space extends Model
{
    /** @use HasFactory<\Database\Factories\SpaceFactory> */
    use HasFactory, SoftDeletes;

    public const STATUSES = ['available', 'unavailable', 'maintenance', 'inactive'];

    protected function casts(): array
    {
        return [
            'requires_approval' => 'boolean',
            'has_cleaning_required' => 'boolean',
            'has_deposit' => 'boolean',
            'deposit_amount' => 'decimal:2',
            'price' => 'decimal:2',
            'is_public' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(SpaceReservation::class);
    }

    public function maintenanceRecords(): HasMany
    {
        return $this->hasMany(SpaceMaintenanceRecord::class);
    }

    public function cleaningRecords(): HasMany
    {
        return $this->hasMany(SpaceCleaningRecord::class);
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
