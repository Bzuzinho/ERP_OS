<?php

namespace App\Services\Documents;

use App\Models\Document;
use App\Models\DocumentAccessRule;
use App\Models\User;

class DocumentAccessService
{
    public function canView(User $user, Document $document): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Admin users with view permission can see all documents regardless of visibility
        if ($user->can('admin.access') && $user->can('documents.view')) {
            return true;
        }

        if ($document->visibility === 'public') {
            return true;
        }

        if ($document->visibility === 'internal') {
            return false;
        }

        if ($document->visibility === 'portal') {
            return $this->isRelatedToUser($user, $document) || $this->hasRule($user, $document, ['view', 'download', 'manage']);
        }

        if ($document->visibility === 'restricted') {
            return $this->hasRule($user, $document, ['view', 'download', 'manage']);
        }

        return false;
    }

    public function canDownload(User $user, Document $document): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Admin users with download permission bypass visibility checks
        if ($user->can('admin.access') && $user->can('documents.download')) {
            return true;
        }

        if (! $this->canView($user, $document)) {
            return false;
        }

        return $document->visibility !== 'restricted'
            || $this->hasRule($user, $document, ['download', 'manage']);
    }

    public function canManage(User $user, Document $document): bool
    {
        if ($user->hasRole('super_admin') || $user->can('documents.manage_access')) {
            return true;
        }

        return $this->hasRule($user, $document, ['manage']);
    }

    private function isRelatedToUser(User $user, Document $document): bool
    {
        if (! $document->related_type || ! $document->related_id) {
            return false;
        }

        $contactIds = $user->contacts()->pluck('id');

        if ($document->related_type === 'App\\Models\\Contact' && $contactIds->contains($document->related_id)) {
            return true;
        }

        if ($document->related_type === 'App\\Models\\Ticket') {
            return $user->id === $document->uploader?->id;
        }

        return false;
    }

    private function hasRule(User $user, Document $document, array $permissions): bool
    {
        $contactIds = $user->contacts()->pluck('id');
        $roleNames = $user->roles->pluck('name');

        return $document->accessRules()
            ->whereIn('permission', $permissions)
            ->where(function ($query) use ($user, $contactIds, $roleNames) {
                $query
                    ->orWhere('user_id', $user->id)
                    ->orWhereIn('contact_id', $contactIds)
                    ->orWhereIn('role_name', $roleNames);
            })
            ->where(function ($query) {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->exists();
    }
}
