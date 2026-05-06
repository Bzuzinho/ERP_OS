<?php

namespace App\Services\Notifications;

use App\Models\Department;
use App\Models\ServiceArea;
use App\Models\Team;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Collection;

class NotificationRecipientResolver
{
    public function resolveForTicket(Ticket $ticket, ?User $actor = null): Collection
    {
        $userIds = collect();

        if ($ticket->assigned_to) {
            $userIds->push((int) $ticket->assigned_to);
        }

        if ($ticket->created_by && (! $actor || (int) $actor->id !== (int) $ticket->created_by)) {
            $creator = User::query()->find($ticket->created_by);
            if ($creator && ! $creator->hasAnyRole(['cidadao', 'associacao', 'empresa'])) {
                $userIds->push((int) $ticket->created_by);
            }
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

        if (method_exists($ticket, 'participants')) {
            $participants = $ticket->participants()->get();
            foreach ($participants as $participant) {
                if (isset($participant->user_id) && $participant->user_id) {
                    $userIds->push((int) $participant->user_id);
                }
                if (isset($participant->contact_id) && $participant->contact_id) {
                    $contactUserId = $participant->contact?->user_id;
                    if ($contactUserId) {
                        $userIds->push((int) $contactUserId);
                    }
                }
            }
        }

        $resolved = User::query()
            ->whereIn('id', $userIds->filter()->unique()->values())
            ->where('is_active', true)
            ->when($ticket->organization_id, fn ($query) => $query->where('organization_id', $ticket->organization_id))
            ->get();

        if ($resolved->isEmpty() && $ticket->source === 'portal') {
            $attendimentoArea = ServiceArea::query()
                ->where('organization_id', $ticket->organization_id)
                ->where('slug', 'atendimento')
                ->first();

            if ($attendimentoArea) {
                $resolved = User::query()
                    ->whereHas('serviceAreas', fn ($query) => $query->where('service_areas.id', $attendimentoArea->id))
                    ->where('is_active', true)
                    ->where('organization_id', $ticket->organization_id)
                    ->get();
            }

            if ($resolved->isEmpty()) {
                $resolved = User::query()
                    ->where('organization_id', $ticket->organization_id)
                    ->where('is_active', true)
                    ->whereHas('roles', fn ($query) => $query->whereIn('name', ['admin_junta', 'administrativo']))
                    ->get();
            }
        }

        return $resolved
            ->when($actor, fn (Collection $users) => $users->where('id', '!=', $actor->id))
            ->unique('id')
            ->values();
    }
}
