<?php

namespace App\Actions\Documents;

use App\Models\Document;
use App\Models\DocumentAccessRule;
use App\Models\User;
use App\Services\Tickets\ActivityLogger;

class GrantDocumentAccessAction
{
    public function __construct(
        private readonly ActivityLogger $activityLogger,
    ) {
    }

    public function execute(Document $document, User $user, array $data): DocumentAccessRule
    {
        $rule = $document->accessRules()->create([
            ...$data,
            'created_by' => $user->id,
        ]);

        $this->activityLogger->log(
            subject: $document,
            action: 'document.access_granted',
            user: $user,
            organization: $document->organization,
            newValues: $rule->only(['user_id', 'contact_id', 'role_name', 'permission', 'expires_at']),
            description: 'Regra de acesso ao documento criada.',
        );

        return $rule;
    }
}
