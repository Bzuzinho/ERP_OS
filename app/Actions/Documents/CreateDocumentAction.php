<?php

namespace App\Actions\Documents;

use App\Models\Document;
use App\Models\User;
use App\Services\Documents\DocumentStorageService;
use App\Services\Tickets\ActivityLogger;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class CreateDocumentAction
{
    public function __construct(
        private readonly DocumentStorageService $storageService,
        private readonly ActivityLogger $activityLogger,
    ) {
    }

    public function execute(User $user, array $data, UploadedFile $file): Document
    {
        return DB::transaction(function () use ($user, $data, $file) {
            $storedFile = $this->storageService->store($file, $user->organization_id);

            $document = Document::create([
                ...$data,
                ...$storedFile,
                'organization_id' => $user->organization_id,
                'uploaded_by' => $user->id,
                'current_version' => 1,
                'status' => $data['status'] ?? 'active',
                'is_active' => true,
            ]);

            $document->versions()->create([
                'version' => 1,
                ...$storedFile,
                'uploaded_by' => $user->id,
                'notes' => $data['version_notes'] ?? null,
            ]);

            $this->activityLogger->log(
                subject: $document,
                action: 'document.created',
                user: $user,
                organization: $user->organization,
                newValues: $document->only(['title', 'visibility', 'status', 'current_version']),
                description: 'Documento criado com sucesso.',
            );

            return $document;
        });
    }
}
