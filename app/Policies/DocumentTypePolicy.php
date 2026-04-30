<?php

namespace App\Policies;

use App\Models\DocumentType;
use App\Models\User;

class DocumentTypePolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->hasRole('super_admin') ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->can('document_types.view');
    }

    public function view(User $user, DocumentType $documentType): bool
    {
        return $user->can('document_types.view');
    }

    public function create(User $user): bool
    {
        return $user->can('document_types.create');
    }

    public function update(User $user, DocumentType $documentType): bool
    {
        return $user->can('document_types.update');
    }

    public function delete(User $user, DocumentType $documentType): bool
    {
        return $user->can('document_types.delete');
    }
}
