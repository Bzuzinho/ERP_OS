<?php

namespace App\Services\Documents;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentStorageService
{
    public function store(UploadedFile $file, ?int $organizationId): array
    {
        $organizationSegment = (string) ($organizationId ?? 'global');
        $basePath = sprintf('documents/%s/%s/%s', $organizationSegment, now()->format('Y'), now()->format('m'));

        $safeName = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $extension = $file->getClientOriginalExtension();
        $generatedName = sprintf('%s-%s.%s', Str::uuid(), $safeName ?: 'documento', $extension ?: 'bin');

        $storedPath = Storage::disk('local')->putFileAs($basePath, $file, $generatedName);

        return [
            'file_path' => $storedPath,
            'file_name' => $generatedName,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'size' => $file->getSize(),
        ];
    }
}
