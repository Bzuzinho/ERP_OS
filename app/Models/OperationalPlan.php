<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'organization_id',
    'title',
    'slug',
    'description',
    'plan_type',
    'status',
    'visibility',
    'start_date',
    'end_date',
    'owner_user_id',
    'department_id',
    'team_id',
    'related_ticket_id',
    'related_space_id',
    'budget_estimate',
    'progress_percent',
    'approved_by',
    'approved_at',
    'cancelled_by',
    'cancelled_at',
    'cancellation_reason',
    'completed_by',
    'completed_at',
    'created_by',
])]
class OperationalPlan extends Model
{
    /** @use HasFactory<\Database\Factories\OperationalPlanFactory> */
    use HasFactory, SoftDeletes;

    public const TYPES = [
        'activity',
        'maintenance',
        'cleaning',
        'public_event',
        'inspection',
        'campaign',
        'project',
        'emergency',
        'administrative',
        'other',
    ];

    public const STATUSES = [
        'draft',
        'pending_approval',
        'approved',
        'scheduled',
        'in_progress',
        'completed',
        'cancelled',
        'archived',
    ];

    public const VISIBILITIES = ['public', 'portal', 'internal', 'restricted'];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'approved_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'completed_at' => 'datetime',
            'budget_estimate' => 'decimal:2',
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

    public function relatedTicket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class, 'related_ticket_id');
    }

    public function relatedSpace(): BelongsTo
    {
        return $this->belongsTo(Space::class, 'related_space_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function canceller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function completer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function planTasks(): HasMany
    {
        return $this->hasMany(OperationalPlanTask::class);
    }

    public function tasks(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, 'operational_plan_tasks')
            ->withPivot(['position', 'is_milestone', 'weight'])
            ->withTimestamps();
    }

    public function participants(): HasMany
    {
        return $this->hasMany(OperationalPlanParticipant::class);
    }

    public function resources(): HasMany
    {
        return $this->hasMany(OperationalPlanResource::class);
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'related', 'related_type', 'related_id');
    }
}
