<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'recurring_operation_id',
    'run_at',
    'status',
    'generated_task_id',
    'generated_event_id',
    'error_message',
    'executed_by',
    'executed_at',
])]
class RecurringOperationRun extends Model
{
    /** @use HasFactory<\Database\Factories\RecurringOperationRunFactory> */
    use HasFactory;

    public const STATUSES = ['pending', 'executed', 'failed', 'skipped'];

    protected function casts(): array
    {
        return [
            'run_at' => 'datetime',
            'executed_at' => 'datetime',
        ];
    }

    public function recurringOperation(): BelongsTo
    {
        return $this->belongsTo(RecurringOperation::class);
    }

    public function generatedTask(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'generated_task_id');
    }

    public function generatedEvent(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'generated_event_id');
    }

    public function executedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'executed_by');
    }
}
