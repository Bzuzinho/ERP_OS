<?php

namespace App\Services\Planning;

use App\Models\RecurringOperation;
use Carbon\Carbon;

class RecurringOperationScheduler
{
    public function calculateNextRunAt(RecurringOperation $operation, ?Carbon $reference = null): ?Carbon
    {
        $base = $reference
            ?? ($operation->last_run_at ? Carbon::parse($operation->last_run_at) : Carbon::parse($operation->start_date)->startOfDay());

        $interval = max(1, (int) $operation->interval);
        $next = match ($operation->frequency) {
            'daily' => $base->copy()->addDays($interval),
            'weekly' => $this->nextWeeklyRun($operation, $base, $interval),
            'monthly' => $this->nextMonthlyRun($operation, $base, $interval),
            'yearly' => $base->copy()->addYears($interval),
            default => $base->copy()->addWeek(),
        };

        if ($operation->end_date && $next->greaterThan(Carbon::parse($operation->end_date)->endOfDay())) {
            return null;
        }

        return $next;
    }

    private function nextWeeklyRun(RecurringOperation $operation, Carbon $base, int $interval): Carbon
    {
        $startDate = Carbon::parse($operation->start_date)->startOfDay();
        $weekdays = collect($operation->weekdays ?? [strtolower($startDate->englishDayOfWeek)])
            ->map(fn ($day) => $this->normalizeWeekday($day))
            ->filter(fn ($day) => $day !== null)
            ->values();

        if ($weekdays->isEmpty()) {
            $weekdays = collect([$startDate->dayOfWeek]);
        }

        $candidate = $base->copy()->startOfDay()->addDay();

        for ($i = 0; $i < 730; $i++) {
            $weekOffset = (int) floor($startDate->copy()->startOfWeek()->diffInDays($candidate->copy()->startOfWeek()) / 7);
            if ($weekOffset % $interval === 0 && $weekdays->contains($candidate->dayOfWeek)) {
                return $candidate;
            }

            $candidate->addDay();
        }

        return $base->copy()->addWeeks($interval);
    }

    private function nextMonthlyRun(RecurringOperation $operation, Carbon $base, int $interval): Carbon
    {
        $anchorDay = (int) ($operation->day_of_month ?: Carbon::parse($operation->start_date)->day);
        $candidate = $base->copy()->addMonthsNoOverflow($interval);
        $candidate->day(min($anchorDay, $candidate->daysInMonth));

        return $candidate;
    }

    private function normalizeWeekday(mixed $day): ?int
    {
        if (is_numeric($day)) {
            $value = (int) $day;

            return $value >= 0 && $value <= 6 ? $value : null;
        }

        $map = [
            'sun' => 0,
            'sunday' => 0,
            'mon' => 1,
            'monday' => 1,
            'tue' => 2,
            'tuesday' => 2,
            'wed' => 3,
            'wednesday' => 3,
            'thu' => 4,
            'thursday' => 4,
            'fri' => 5,
            'friday' => 5,
            'sat' => 6,
            'saturday' => 6,
        ];

        $key = strtolower((string) $day);

        return $map[$key] ?? null;
    }
}
