<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'organization_id',
    'reference',
    'created_by',
    'contact_id',
    'assigned_to',
    'department_id',
    'service_area_id',
    'team_id',
    'category',
    'subcategory',
    'priority',
    'status',
    'title',
    'description',
    'location_text',
    'source',
    'visibility',
    'due_date',
    'closed_at',
    'closed_by',
])]
class Ticket extends Model
{
    use BelongsToOrganization, HasFactory, SoftDeletes;

    public const STATUSES = [
        'novo',
        'em_analise',
        'aguarda_informacao',
        'encaminhado',
        'em_execucao',
        'agendado',
        'resolvido',
        'fechado',
        'cancelado',
        'indeferido',
    ];

    public const PRIORITIES = [
        'low',
        'normal',
        'high',
        'urgent',
    ];

    public const SOURCES = [
        'portal',
        'internal',
        'phone',
        'email',
        'presencial',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'closed_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function serviceArea(): BelongsTo
    {
        return $this->belongsTo(ServiceArea::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(TicketStatusHistory::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function spaceMaintenanceRecords(): HasMany
    {
        return $this->hasMany(SpaceMaintenanceRecord::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class, 'related_ticket_id');
    }

    public function operationalPlans(): HasMany
    {
        return $this->hasMany(OperationalPlan::class, 'related_ticket_id');
    }

    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class, 'related_ticket_id');
    }

    public function inventoryLoans(): HasMany
    {
        return $this->hasMany(InventoryLoan::class, 'related_ticket_id');
    }

    public function inventoryBreakages(): HasMany
    {
        return $this->hasMany(InventoryBreakage::class, 'related_ticket_id');
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

    public function notifications(): MorphMany
    {
        return $this->morphMany(Notification::class, 'notifiable');
    }
}
