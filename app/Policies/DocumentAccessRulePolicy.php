<?php

namespace App\Policies;

use App\Models\DocumentAccessRule;
use App\Models\User;

class DocumentAccessRulePolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->hasRole('super_admin') ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->can('documents.manage_access');
    }

    public function create(User $user): bool
    {
        return $user->can('documents.manage_access');
    }

    public function delete(User $user, DocumentAccessRule $documentAccessRule): bool
    {
        return $user->can('documents.manage_access') || $user->can('manageAccess', $documentAccessRule->document);
    }
}
