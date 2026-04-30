<?php

namespace App\Services\Spaces;

use App\Models\SpaceReservation;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class SpaceAvailabilityService
{
    public function isAvailable(
        int $spaceId,
        CarbonInterface|string $startAt,
        CarbonInterface|string $endAt,
        ?int $ignoreReservationId = null,
    ): array {
        $start = $startAt instanceof CarbonInterface ? $startAt : Carbon::parse($startAt);
        $end = $endAt instanceof CarbonInterface ? $endAt : Carbon::parse($endAt);

        if ($end->lessThanOrEqualTo($start)) {
            throw new InvalidArgumentException('A data de fim deve ser posterior a data de inicio.');
        }

        $approvedConflicts = $this->queryConflicts($spaceId, $start, $end, ['approved'], $ignoreReservationId);
        $requestedConflicts = $this->queryConflicts($spaceId, $start, $end, ['requested'], $ignoreReservationId);

        return [
            'available' => $approvedConflicts->isEmpty(),
            'approvedConflicts' => $approvedConflicts,
            'requestedConflicts' => $requestedConflicts,
            'conflicts' => $approvedConflicts->concat($requestedConflicts),
        ];
    }

    public function hasApprovedConflict(
        int $spaceId,
        CarbonInterface|string $startAt,
        CarbonInterface|string $endAt,
        ?int $ignoreReservationId = null,
    ): bool {
        $result = $this->isAvailable($spaceId, $startAt, $endAt, $ignoreReservationId);

        return ! $result['approvedConflicts']->isEmpty();
    }

    private function queryConflicts(
        int $spaceId,
        CarbonInterface $startAt,
        CarbonInterface $endAt,
        array $statuses,
        ?int $ignoreReservationId,
    ): Collection {
        return SpaceReservation::query()
            ->where('space_id', $spaceId)
            ->whereIn('status', $statuses)
            ->when($ignoreReservationId, fn ($query) => $query->whereKeyNot($ignoreReservationId))
            ->where(function ($query) use ($startAt, $endAt) {
                $query->where('start_at', '<', $endAt)
                    ->where('end_at', '>', $startAt)
                    ->orWhere(function ($coveringQuery) use ($startAt, $endAt) {
                        $coveringQuery->where('start_at', '<=', $startAt)
                            ->where('end_at', '>=', $endAt);
                    });
            })
            ->select(['id', 'space_id', 'status', 'start_at', 'end_at', 'purpose'])
            ->orderBy('start_at')
            ->get();
    }
}
