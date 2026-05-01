<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'operational_plan_id',
    'task_id',
    'position',
    'is_milestone',
    'weight',
])]
class OperationalPlanTask extends Model
{
    /** @use HasFactory<\Database\Factories\OperationalPlanTaskFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'is_milestone' => 'boolean',
        ];
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(OperationalPlan::class, 'operational_plan_id');
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }
}
