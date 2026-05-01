<?php

namespace App\Services\Planning;

use App\Models\OperationalPlan;

class OperationalPlanProgressService
{
    public function recalculate(OperationalPlan $plan): int
    {
        $plan->loadMissing('tasks');

        $tasks = $plan->tasks;

        if ($tasks->isEmpty()) {
            return (int) ($plan->progress_percent ?? 0);
        }

        $hasWeights = $tasks->contains(fn ($task) => (int) ($task->pivot?->weight ?? 0) > 0);

        if ($hasWeights) {
            $totalWeight = (int) $tasks->sum(fn ($task) => max(1, (int) ($task->pivot?->weight ?? 1)));
            $doneWeight = (int) $tasks
                ->filter(fn ($task) => $task->status === 'done')
                ->sum(fn ($task) => max(1, (int) ($task->pivot?->weight ?? 1)));

            $progress = $totalWeight > 0 ? (int) round(($doneWeight / $totalWeight) * 100) : 0;
        } else {
            $doneCount = $tasks->where('status', 'done')->count();
            $progress = (int) round(($doneCount / $tasks->count()) * 100);
        }

        $progress = max(0, min(100, $progress));

        $plan->forceFill(['progress_percent' => $progress])->save();

        return $progress;
    }
}
