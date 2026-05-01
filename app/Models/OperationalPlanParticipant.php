<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'operational_plan_id',
    'user_id',
    'employee_id',
    'team_id',
    'role',
])]
class OperationalPlanParticipant extends Model
{
    /** @use HasFactory<\Database\Factories\OperationalPlanParticipantFactory> */
    use HasFactory;

    public function plan(): BelongsTo
    {
        return $this->belongsTo(OperationalPlan::class, 'operational_plan_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
