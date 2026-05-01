<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

#[Fillable([
    'organization_id',
    'employee_id',
    'date',
    'status',
    'check_in',
    'check_out',
    'worked_minutes',
    'source',
    'notes',
    'validated_by',
    'validated_at',
    'created_by',
])]
class AttendanceRecord extends Model
{
    use HasFactory;

    public const STATUSES = [
        'present',
        'absent',
        'vacation',
        'sick_leave',
        'justified_absence',
        'unjustified_absence',
        'remote',
        'off',
        'training',
        'overtime',
    ];

    public const SOURCES = [
        'manual',
        'import',
        'system',
        'self_service',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'validated_at' => 'datetime',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
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
