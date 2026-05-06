<?php

namespace App\Services\Notifications;

use App\Models\Department;
use App\Models\ServiceArea;
use App\Models\Team;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Collection;

class TicketNotificationRecipientResolver
{
    public function resolveCitizenRecipient(Ticket $ticket, ?User $actor = null): ?User
    {
        $candidate = null;

        if ($ticket->contact?->user_id) {
            $candidate = User::query()->find($ticket->contact->user_id);
        }

        if (! $candidate && $ticket->created_by) {
            $candidate = User::query()->find($ticket->created_by);
        }

        if (! $candidate || ! $candidate->is_active) {
            return null;
        }

        if ((int) $candidate->organization_id !== (int) $ticket->organization_id) {
            return null;
        }

        if ($actor && (int) $candidate->id === (int) $actor->id) {
            return null;
        }

        return $candidate;
    }

    public function resolveInternalRecipients(Ticket $ticket, ?User $actor = null): Collection
    {
        $userIds = collect();

        if ($ticket->assigned_to) {
            $userIds->push((int) $ticket->assigned_to);
        }

        if ($ticket->service_area_id) {
            $serviceArea = ServiceArea::query()->with('users:id,is_active,organization_id')->find($ticket->service_area_id);
            if ($serviceArea) {
                $userIds = $userIds->merge($serviceArea->users->pluck('id')->all());
            }
        }

        if ($ticket->department_id) {
            $department = Department::query()->find($ticket->department_id);
            if ($department) {
                $departmentUserIds = User::query()
                    ->where('organization_id', $department->organization_id)
                    ->whereHas('employee', fn ($query) => $query
                        ->where('department_id', $department->id)
                        ->where('is_active', true))
                    ->pluck('id');

                $userIds = $userIds->merge($departmentUserIds);
            }
        }

        if ($ticket->team_id) {
            $team = Team::query()->find($ticket->team_id);
            if ($team) {
                $teamUserIds = User::query()
                    ->where('organization_id', $team->organization_id)
                    ->whereHas('employee.teamMemberships', fn ($query) => $query
                        ->where('team_id', $team->id)
                        ->where('is_active', true))
                    ->pluck('id');

                $userIds = $userIds->merge($teamUserIds);
            }
        }

        $resolved = User::query()
            ->whereIn('id', $userIds->filter()->unique()->values())
            ->where('is_active', true)
            ->where('organization_id', $ticket->organization_id)
            ->get();

        if ($resolved->isEmpty()) {
            $resolved = User::query()
                ->where('organization_id', $ticket->organization_id)
                ->where('is_active', true)
                ->whereHas('roles', fn ($query) => $query->whereIn('name', ['admin_junta', 'administrativo', 'super_admin']))
                ->get();
        }

        return $resolved
            ->when($actor, fn (Collection $users) => $users->where('id', '!=', $actor->id))
            ->unique('id')
            ->values();
    }

    public function resolveTicketCreatedRecipients(Ticket $ticket, ?User $actor = null): Collection
    {
        return $this->resolveInternalRecipients($ticket, $actor);
    }
}
