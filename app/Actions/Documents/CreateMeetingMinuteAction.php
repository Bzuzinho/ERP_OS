<?php

namespace App\Actions\Documents;

use App\Models\Document;
use App\Models\MeetingMinute;
use App\Models\User;
use App\Services\Tickets\ActivityLogger;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class CreateMeetingMinuteAction
{
    public function __construct(
        private readonly CreateDocumentAction $createDocumentAction,
        private readonly ActivityLogger $activityLogger,
    ) {
    }

    public function execute(User $user, array $data, ?UploadedFile $file = null): MeetingMinute
    {
        return DB::transaction(function () use ($user, $data, $file) {
            $document = null;

            if (! empty($data['document_id'])) {
                $document = Document::query()->find($data['document_id']);
            }

            if (! $document && $file) {
                $document = $this->createDocumentAction->execute($user, [
                    'title' => $data['title'],
                    'description' => $data['summary'] ?? null,
                    'document_type_id' => $data['document_type_id'] ?? null,
                    'visibility' => $data['visibility'] ?? 'internal',
                    'status' => 'active',
                    'related_type' => 'App\\Models\\Event',
                    'related_id' => $data['event_id'],
                ], $file);
            }

            $minute = MeetingMinute::query()->create([
                'organization_id' => $user->organization_id,
                'event_id' => $data['event_id'],
                'document_id' => $document?->id,
                'title' => $data['title'],
                'summary' => $data['summary'] ?? null,
                'status' => 'draft',
                'created_by' => $user->id,
            ]);

            $this->activityLogger->log(
                subject: $minute,
                action: 'meeting_minute.created',
                user: $user,
                organization: $user->organization,
                newValues: $minute->only(['event_id', 'title', 'status']),
                description: 'Ata criada e associada ao evento.',
            );

            return $minute;
        });
    }
}
