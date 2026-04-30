<?php

namespace App\Services\Tickets;

use App\Models\Organization;
use App\Models\Ticket;

class TicketReferenceGenerator
{
    public function generate(?Organization $organization): string
    {
        $year = now()->year;
        $organizationCode = strtoupper(trim((string) ($organization?->code ?: 'JF')));
        $prefix = sprintf('%s-%d-', $organizationCode, $year);

        $query = Ticket::withTrashed()
            ->where('reference', 'like', $prefix.'%');

        if ($organization?->id) {
            $query->where('organization_id', $organization->id);
        } else {
            $query->whereNull('organization_id');
        }

        $lastReference = $query->orderByDesc('reference')->value('reference');
        $sequence = $lastReference ? ((int) substr($lastReference, -6)) + 1 : 1;

        do {
            $reference = $prefix.str_pad((string) $sequence, 6, '0', STR_PAD_LEFT);
            $exists = Ticket::withTrashed()->where('reference', $reference)->exists();
            $sequence++;
        } while ($exists);

        return $reference;
    }
}
