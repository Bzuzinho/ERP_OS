<?php

namespace App\Actions\Documents;

use App\Models\Document;
use App\Models\User;
use App\Services\Tickets\ActivityLogger;

class ArchiveDocumentAction
{
    public function __construct(
        private readonly ActivityLogger $activityLogger,
    ) {
    }

    public function execute(Document $document, User $user): Document
    {
        $oldValues = $document->only(['status', 'is_active']);

        $document->update([
            'status' => 'archived',
            'is_active' => false,
        ]);

        $this->activityLogger->log(
            subject: $document,
            action: 'document.archived',
            user: $user,
            organization: $document->organization,
            oldValues: $oldValues,
            newValues: $document->only(['status', 'is_active']),
            description: 'Documento arquivado.',
        );

        return $document;
    }
}
