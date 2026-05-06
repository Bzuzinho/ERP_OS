<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\User;
use App\Services\Documents\DocumentAccessService;
use App\Support\OrganizationScope;

class DocumentPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->hasRole('super_admin') ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->can('documents.view') || $user->hasAnyRole(['cidadao', 'associacao', 'empresa']);
    }

    public function view(User $user, Document $document): bool
    {
        if (! OrganizationScope::sameOrganization($document->organization_id, $user)) {
            return false;
        }

        return app(DocumentAccessService::class)->canView($user, $document);
    }

    public function create(User $user): bool
    {
        return $user->can('documents.upload');
    }

    public function update(User $user, Document $document): bool
    {
        if (! OrganizationScope::sameOrganization($document->organization_id, $user)) {
            return false;
        }

        return $user->can('documents.update') || app(DocumentAccessService::class)->canManage($user, $document);
    }

    public function delete(User $user, Document $document): bool
    {
        return $user->can('documents.delete')
            && OrganizationScope::sameOrganization($document->organization_id, $user);
    }

    public function download(User $user, Document $document): bool
    {
        if (! OrganizationScope::sameOrganization($document->organization_id, $user)) {
            return false;
        }

        return app(DocumentAccessService::class)->canDownload($user, $document);
    }

    public function manageAccess(User $user, Document $document): bool
    {
        if (! OrganizationScope::sameOrganization($document->organization_id, $user)) {
            return false;
        }

        return app(DocumentAccessService::class)->canManage($user, $document);
    }
}
