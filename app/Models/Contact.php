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
    'user_id',
    'type',
    'name',
    'nif',
    'email',
    'phone',
    'mobile',
    'notes',
    'is_active',
])]
class Contact extends Model
{
    use HasFactory, SoftDeletes;

    public const TYPES = [
        'citizen',
        'association',
        'company',
        'supplier',
        'institution',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(ContactAddress::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class, 'related_contact_id');
    }

    public function spaceReservations(): HasMany
    {
        return $this->hasMany(SpaceReservation::class);
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'related', 'related_type', 'related_id');
    }
}
