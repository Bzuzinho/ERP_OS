<?php

namespace App\Data;

use App\Models\User;

readonly class DashboardContext
{
    public function __construct(
        public User $user,
        public ?int $organizationId,
        public ?int $departmentId,
        public ?int $serviceAreaId,
        public bool $allMyScopes,
    ) {
    }
}
