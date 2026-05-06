<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable([
    'organization_id',
    'type',
    'title',
    'message',
    'notifiable_type',
    'notifiable_id',
    'action_url',
    'priority',
    'data',
    'created_by',
])]
class Notification extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'data' => 'array',
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

    public function recipients(): HasMany
    {
        return $this->hasMany(NotificationRecipient::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'notification_recipients')
            ->withPivot('seen_at', 'read_at', 'archived_at')
            ->withTimestamps();
    }

    public function notifiable(): MorphTo
    {
        return $this->morphTo();
    }
}
