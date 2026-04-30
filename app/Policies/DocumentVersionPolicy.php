<?php

namespace App\Policies;

use App\Models\DocumentVersion;
use App\Models\User;

class DocumentVersionPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->hasRole('super_admin') ? true : null;
    }

    public function view(User $user, DocumentVersion $documentVersion): bool
    {
        return $user->can('documents.view') || $user->can('view', $documentVersion->document);
    }

    public function create(User $user): bool
    {
        return $user->can('documents.update');
    }
}
