<?php

namespace App\Models\Concerns;

use App\Models\Organization;
use App\Models\User;
use App\Support\OrganizationScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToOrganization
{
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function scopeForOrganization(Builder $query, ?int $organizationId): Builder
    {
        if ($organizationId === null) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where($query->getModel()->qualifyColumn('organization_id'), $organizationId);
    }

    public function scopeVisibleToUser(Builder $query, User $user): Builder
    {
        return OrganizationScope::apply(
            $query,
            $user,
            $query->getModel()->qualifyColumn('organization_id'),
        );
    }

    public function belongsToSameOrganizationAs(User $user): bool
    {
        return OrganizationScope::sameOrganization($this->organization_id, $user);
    }
}