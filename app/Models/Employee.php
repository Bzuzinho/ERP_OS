<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
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
    'user_id',
    'department_id',
    'employee_number',
    'role_title',
    'employment_type',
    'start_date',
    'end_date',
    'phone',
    'emergency_contact_name',
    'emergency_contact_phone',
    'notes',
    'is_active',
])]
class Employee extends Model
{
    use BelongsToOrganization, HasFactory, SoftDeletes;

    public const EMPLOYMENT_TYPES = [
        'permanent',
        'contract',
        'temporary',
        'volunteer',
        'external',
        'other',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function teamMemberships(): HasMany
    {
        return $this->hasMany(TeamMember::class);
    }

    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'team_members')
            ->withPivot('role', 'joined_at', 'left_at', 'is_active')
            ->withTimestamps();
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(EmployeeSchedule::class);
    }

    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function taskAssignments(): HasMany
    {
        return $this->hasMany(EmployeeTaskAssignment::class);
    }

    public function assignedTasks(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, 'employee_task_assignments')
            ->withPivot('role', 'assigned_at', 'removed_at', 'is_active')
            ->withTimestamps();
    }

    public function eventAssignments(): HasMany
    {
        return $this->hasMany(EmployeeEventAssignment::class);
    }

    public function assignedEvents(): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'employee_event_assignments')
            ->withPivot('role', 'assigned_at', 'removed_at', 'is_active')
            ->withTimestamps();
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
