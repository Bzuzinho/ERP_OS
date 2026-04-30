<?php

namespace App\Actions\Documents;

use App\Models\DocumentAccessRule;
use App\Models\User;
use App\Services\Tickets\ActivityLogger;

class RevokeDocumentAccessAction
{
    public function __construct(
        private readonly ActivityLogger $activityLogger,
    ) {
    }

    public function execute(DocumentAccessRule $rule, User $user): void
    {
        $document = $rule->document;

        $oldValues = $rule->only(['user_id', 'contact_id', 'role_name', 'permission', 'expires_at']);
        $rule->delete();

        $this->activityLogger->log(
            subject: $document,
            action: 'document.access_revoked',
            user: $user,
            organization: $document->organization,
            oldValues: $oldValues,
            description: 'Regra de acesso ao documento removida.',
        );
    }
}
