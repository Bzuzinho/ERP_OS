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
    'ticket_id',
    'assigned_to',
    'created_by',
    'title',
    'description',
    'status',
    'priority',
    'start_date',
    'due_date',
    'completed_at',
    'completed_by',
])]
class Task extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUSES = ['pending', 'in_progress', 'waiting', 'done', 'cancelled'];

    public const PRIORITIES = ['low', 'normal', 'high', 'urgent'];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'due_date' => 'date',
            'completed_at' => 'datetime',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public function checklists(): HasMany
    {
        return $this->hasMany(TaskChecklist::class);
    }

    public function spaceMaintenanceRecords(): HasMany
    {
        return $this->hasMany(SpaceMaintenanceRecord::class);
    }

    public function spaceCleaningRecords(): HasMany
    {
        return $this->hasMany(SpaceCleaningRecord::class);
    }

    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class, 'related_task_id');
    }

    public function inventoryLoans(): HasMany
    {
        return $this->hasMany(InventoryLoan::class, 'related_task_id');
    }

    public function inventoryBreakages(): HasMany
    {
        return $this->hasMany(InventoryBreakage::class, 'related_task_id');
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

    public function employeeAssignments(): HasMany
    {
        return $this->hasMany(EmployeeTaskAssignment::class);
    }

    public function assignedEmployees(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Employee::class, 'employee_task_assignments')
            ->withPivot('role', 'assigned_at', 'removed_at', 'is_active')
            ->withTimestamps();
    }

    public function operationalPlans(): BelongsToMany
    {
        return $this->belongsToMany(OperationalPlan::class, 'operational_plan_tasks')
            ->withPivot(['position', 'is_milestone', 'weight'])
            ->withTimestamps();
    }

    public function recurringOperationRun(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(RecurringOperationRun::class, 'generated_task_id');
    }
}
