<?php

namespace App\Policies;

use App\Models\SpaceReservation;
use App\Models\User;

class SpaceReservationPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->hasRole('super_admin') ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->can('spaces.view') || $user->can('spaces.reserve') || $user->hasAnyRole(['cidadao', 'associacao', 'empresa']);
    }

    public function view(User $user, SpaceReservation $spaceReservation): bool
    {
        if ($user->can('spaces.approve_reservation') || $user->can('spaces.cancel_reservation') || $user->can('spaces.update')) {
            return true;
        }

        $contactIds = $user->contacts()->pluck('id');

        return $spaceReservation->requested_by_user_id === $user->id
            || ($spaceReservation->contact_id && $contactIds->contains($spaceReservation->contact_id));
    }

    public function create(User $user): bool
    {
        return $user->can('spaces.reserve') || $user->hasAnyRole(['cidadao', 'associacao', 'empresa']);
    }

    public function update(User $user, SpaceReservation $spaceReservation): bool
    {
        return $user->can('spaces.update');
    }

    public function delete(User $user, SpaceReservation $spaceReservation): bool
    {
        return $user->can('spaces.delete');
    }

    public function approve(User $user, SpaceReservation $spaceReservation): bool
    {
        return $user->can('spaces.approve_reservation');
    }

    public function cancel(User $user, SpaceReservation $spaceReservation): bool
    {
        if ($user->can('spaces.cancel_reservation')) {
            return true;
        }

        $contactIds = $user->contacts()->pluck('id');

        return $spaceReservation->requested_by_user_id === $user->id
            || ($spaceReservation->contact_id && $contactIds->contains($spaceReservation->contact_id));
    }
}
