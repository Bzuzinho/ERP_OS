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
    'operation_type',
    'status',
    'frequency',
    'interval',
    'weekdays',
    'day_of_month',
    'start_date',
    'end_date',
    'next_run_at',
    'last_run_at',
    'owner_user_id',
    'department_id',
    'team_id',
    'related_space_id',
    'task_template',
    'event_template',
    'created_by',
])]
class RecurringOperation extends Model
{
    /** @use HasFactory<\Database\Factories\RecurringOperationFactory> */
    use HasFactory, SoftDeletes;

    public const TYPES = ['task', 'event', 'maintenance', 'cleaning', 'inspection', 'other'];

    public const STATUSES = ['active', 'paused', 'completed', 'cancelled'];

    public const FREQUENCIES = ['daily', 'weekly', 'monthly', 'yearly'];

    protected function casts(): array
    {
        return [
            'weekdays' => 'array',
            'task_template' => 'array',
            'event_template' => 'array',
            'start_date' => 'date',
            'end_date' => 'date',
            'next_run_at' => 'datetime',
            'last_run_at' => 'datetime',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function relatedSpace(): BelongsTo
    {
        return $this->belongsTo(Space::class, 'related_space_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function runs(): HasMany
    {
        return $this->hasMany(RecurringOperationRun::class);
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
