<?php

namespace App\Services\Scopes;

use App\Data\DashboardContext;
use App\Models\Department;
use App\Models\Organization;
use App\Models\ServiceArea;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UserOperationalScopeService
{
    public function organizationsFor(User $user): Collection
    {
        if ($user->hasRole('super_admin')) {
            return Organization::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get();
        }

        $organizationIds = $user->organizations()
            ->wherePivot('is_active', true)
            ->pluck('organizations.id')
            ->map(fn ($id) => (int) $id)
            ->all();

        if ($user->organization_id !== null) {
            $organizationIds[] = (int) $user->organization_id;
        }

        $organizationIds = array_values(array_unique($organizationIds));

        if ($organizationIds === []) {
            return new Collection();
        }

        return Organization::query()
            ->whereIn('id', $organizationIds)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function departmentsFor(User $user, Organization $organization): Collection
    {
        if (! $this->canAccessOrganization($user, $organization)) {
            return new Collection();
        }

        $query = Department::query()
            ->where('organization_id', $organization->id)
            ->where('is_active', true)
            ->orderBy('name');

        if ($this->hasGlobalOrganizationAccess($user, $organization)) {
            return $query->get();
        }

        $departmentIds = DB::table('department_user')
            ->where('user_id', $user->id)
            ->where('organization_id', $organization->id)
            ->where('is_active', true)
            ->pluck('department_id')
            ->all();

        if ($departmentIds === []) {
            $hasScopedDepartments = DB::table('department_user')
                ->where('user_id', $user->id)
                ->where('organization_id', $organization->id)
                ->where('is_active', true)
                ->exists();

            if (! $hasScopedDepartments) {
                return $query->get();
            }

            return new Collection();
        }

        return $query->whereIn('id', $departmentIds)->get();
    }

    public function serviceAreasFor(User $user, Organization $organization, ?Department $department = null): Collection
    {
        if (! $this->canAccessOrganization($user, $organization)) {
            return new Collection();
        }

        if ($department !== null && (int) $department->organization_id !== (int) $organization->id) {
            return new Collection();
        }

        $query = ServiceArea::query()
            ->where('organization_id', $organization->id)
            ->where('is_active', true)
            ->orderBy('name');

        if ($department !== null) {
            $query->where('department_id', $department->id);
        }

        if ($this->hasGlobalOrganizationAccess($user, $organization)) {
            return $query->get();
        }

        $serviceAreaIds = DB::table('service_area_user')
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->where(function ($sub) use ($organization): void {
                $sub->where('organization_id', $organization->id)
                    ->orWhereNull('organization_id');
            })
            ->pluck('service_area_id')
            ->all();

        if ($serviceAreaIds === []) {
            $hasScopedServiceAreas = DB::table('service_area_user')
                ->where('user_id', $user->id)
                ->where('is_active', true)
                ->where(function ($sub) use ($organization): void {
                    $sub->where('organization_id', $organization->id)
                        ->orWhereNull('organization_id');
                })
                ->exists();

            if (! $hasScopedServiceAreas) {
                return $query->get();
            }

            return new Collection();
        }

        return $query->whereIn('id', $serviceAreaIds)->get();
    }

    public function canAccessOrganization(User $user, Organization $organization): bool
    {
        return $this->organizationsFor($user)->contains(fn (Organization $org) => (int) $org->id === (int) $organization->id);
    }

    public function canAccessDepartment(User $user, Department $department): bool
    {
        if ($department->organization_id === null) {
            return false;
        }

        $organization = Organization::query()->find($department->organization_id);

        if (! $organization || ! $this->canAccessOrganization($user, $organization)) {
            return false;
        }

        if ($this->hasGlobalOrganizationAccess($user, $organization)) {
            return true;
        }

        $hasScopedDepartments = DB::table('department_user')
            ->where('user_id', $user->id)
            ->where('organization_id', $organization->id)
            ->where('is_active', true)
            ->exists();

        if (! $hasScopedDepartments) {
            return (int) $department->organization_id === (int) $organization->id;
        }

        return DB::table('department_user')
            ->where('user_id', $user->id)
            ->where('department_id', $department->id)
            ->where('organization_id', $organization->id)
            ->where('is_active', true)
            ->exists();
    }

    public function canAccessServiceArea(User $user, ServiceArea $serviceArea): bool
    {
        if ($serviceArea->organization_id === null) {
            return false;
        }

        $organization = Organization::query()->find($serviceArea->organization_id);

        if (! $organization || ! $this->canAccessOrganization($user, $organization)) {
            return false;
        }

        if ($this->hasGlobalOrganizationAccess($user, $organization)) {
            return true;
        }

        $hasScopedServiceAreas = DB::table('service_area_user')
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->where(function ($query) use ($organization): void {
                $query->where('organization_id', $organization->id)
                    ->orWhereNull('organization_id');
            })
            ->exists();

        if (! $hasScopedServiceAreas) {
            return (int) $serviceArea->organization_id === (int) $organization->id;
        }

        return DB::table('service_area_user')
            ->where('user_id', $user->id)
            ->where('service_area_id', $serviceArea->id)
            ->where('is_active', true)
            ->where(function ($query) use ($organization): void {
                $query->where('organization_id', $organization->id)
                    ->orWhereNull('organization_id');
            })
            ->exists();
    }

    public function hasGlobalOrganizationAccess(User $user, Organization $organization): bool
    {
        return $user->hasGlobalAccessToOrganization($organization);
    }

    public function buildContextTree(User $user): array
    {
        $organizations = $this->organizationsFor($user);

        return [
            'organizations' => $organizations->map(function (Organization $organization) use ($user): array {
                $departments = $this->departmentsFor($user, $organization);

                return [
                    'id' => $organization->id,
                    'name' => $organization->name,
                    'is_default' => (int) optional($user->defaultOrganization())->id === (int) $organization->id,
                    'has_global_access' => $this->hasGlobalOrganizationAccess($user, $organization),
                    'departments' => $departments->map(function (Department $department) use ($user, $organization): array {
                        return [
                            'id' => $department->id,
                            'name' => $department->name,
                            'service_areas' => $this->serviceAreasFor($user, $organization, $department)
                                ->map(fn (ServiceArea $area): array => [
                                    'id' => $area->id,
                                    'name' => $area->name,
                                    'parent_id' => $area->parent_id,
                                ])
                                ->values()
                                ->all(),
                        ];
                    })->values()->all(),
                ];
            })->values()->all(),
        ];
    }

    public function validateContext(User $user, ?int $organizationId, ?int $departmentId, ?int $serviceAreaId): DashboardContext
    {
        $allMyScopes = $organizationId === null && $departmentId === null && $serviceAreaId === null;

        if ($allMyScopes) {
            return new DashboardContext($user, null, null, null, true);
        }

        if ($organizationId === null) {
            throw ValidationException::withMessages([
                'organization_id' => 'O contexto operacional requer uma organização válida.',
            ]);
        }

        $organization = Organization::query()->find($organizationId);

        if (! $organization || ! $this->canAccessOrganization($user, $organization)) {
            throw ValidationException::withMessages([
                'organization_id' => 'Sem acesso à organização selecionada.',
            ]);
        }

        $department = null;
        if ($departmentId !== null) {
            $department = Department::query()->find($departmentId);

            if (! $department || (int) $department->organization_id !== (int) $organization->id || ! $this->canAccessDepartment($user, $department)) {
                throw ValidationException::withMessages([
                    'department_id' => 'Sem acesso ao departamento selecionado.',
                ]);
            }
        }

        if ($serviceAreaId !== null) {
            $serviceArea = ServiceArea::query()->find($serviceAreaId);

            if (! $serviceArea || (int) $serviceArea->organization_id !== (int) $organization->id || ! $this->canAccessServiceArea($user, $serviceArea)) {
                throw ValidationException::withMessages([
                    'service_area_id' => 'Sem acesso ao tema operacional selecionado.',
                ]);
            }

            if ($department !== null && (int) $serviceArea->department_id !== (int) $department->id) {
                throw ValidationException::withMessages([
                    'service_area_id' => 'O tema operacional não pertence ao departamento selecionado.',
                ]);
            }
        }

        return new DashboardContext(
            $user,
            $organization->id,
            $department?->id,
            $serviceAreaId,
            false,
        );
    }
}
