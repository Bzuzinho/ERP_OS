<?php

namespace App\Services\Documents;

use App\Models\Document;

class DocumentVersionService
{
    public function nextVersion(Document $document): int
    {
        $latestVersion = (int) $document->versions()->max('version');

        return max($latestVersion, (int) $document->current_version) + 1;
    }
}
