<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;

class OrganizationScope
{
    public static function organizationIdFor(User $user, ?int $organizationId = null): ?int
    {
        return $organizationId ?? $user->organization_id;
    }

    public static function canBypassOrganizationScope(?User $user): bool
    {
        return $user?->hasRole('super_admin') ?? false;
    }

    public static function apply(Builder $query, User $user, string $column = 'organization_id', ?int $organizationId = null): Builder
    {
        if (self::canBypassOrganizationScope($user) && $organizationId === null) {
            return $query;
        }

        $targetOrganizationId = self::organizationIdFor($user, $organizationId);

        if ($targetOrganizationId === null) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where($column, $targetOrganizationId);
    }

    public static function ensureModelBelongsToUserOrganization(Model $model, User $user, string $column = 'organization_id'): void
    {
        if (self::canBypassOrganizationScope($user)) {
            return;
        }

        abort_unless(self::sameOrganization($model->{$column} ?? null, $user), 404);
    }

    public static function existsRuleForUser(
        string $table,
        User $user,
        string $column = 'id',
        ?int $organizationId = null,
        string $organizationColumn = 'organization_id',
    ): Exists {
        $rule = Rule::exists($table, $column);

        if (self::canBypassOrganizationScope($user) && $organizationId === null) {
            return $rule;
        }

        $targetOrganizationId = self::organizationIdFor($user, $organizationId);

        return $rule->where(function ($query) use ($organizationColumn, $targetOrganizationId) {
            if ($targetOrganizationId === null) {
                $query->whereRaw('1 = 0');

                return;
            }

            $query->where($organizationColumn, $targetOrganizationId);
        });
    }

    public static function sameOrganization(?int $organizationId, User $user): bool
    {
        if (self::canBypassOrganizationScope($user)) {
            return true;
        }

        return $organizationId !== null && (int) $organizationId === (int) $user->organization_id;
    }
}