<?php

namespace App\Actions\Documents;

use App\Models\Document;
use App\Models\DocumentVersion;
use App\Models\User;
use App\Services\Documents\DocumentStorageService;
use App\Services\Documents\DocumentVersionService;
use App\Services\Tickets\ActivityLogger;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class CreateDocumentVersionAction
{
    public function __construct(
        private readonly DocumentStorageService $storageService,
        private readonly DocumentVersionService $versionService,
        private readonly ActivityLogger $activityLogger,
    ) {
    }

    public function execute(Document $document, User $user, UploadedFile $file, ?string $notes = null): DocumentVersion
    {
        return DB::transaction(function () use ($document, $user, $file, $notes) {
            $newVersion = $this->versionService->nextVersion($document);
            $storedFile = $this->storageService->store($file, $document->organization_id);

            $documentVersion = $document->versions()->create([
                'version' => $newVersion,
                ...$storedFile,
                'uploaded_by' => $user->id,
                'notes' => $notes,
            ]);

            $document->update([
                ...$storedFile,
                'current_version' => $newVersion,
                'uploaded_by' => $user->id,
            ]);

            $this->activityLogger->log(
                subject: $document,
                action: 'document.version_created',
                user: $user,
                organization: $document->organization,
                newValues: ['version' => $newVersion],
                description: 'Nova versao de documento carregada.',
            );

            return $documentVersion;
        });
    }
}
