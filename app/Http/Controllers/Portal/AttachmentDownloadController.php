<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Attachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttachmentDownloadController extends Controller
{
    public function __invoke(Request $request, Attachment $attachment): StreamedResponse
    {
        // Portal users can only download their own ticket attachments that are public
        $attachable = $attachment->attachable;

        if (! $attachable) {
            abort(404);
        }

        // Ensure portal user owns the parent ticket
        $this->authorize('view', $attachable);

        // Portal users may only download public attachments
        if ($attachment->visibility === 'internal') {
            abort(403, 'Este anexo não está disponível.');
        }

        abort_unless(Storage::disk('local')->exists($attachment->file_path), 404);

        if ($request->boolean('inline')) {
            return Storage::disk('local')->response(
                $attachment->file_path,
                $attachment->file_name,
                ['Content-Disposition' => 'inline'],
            );
        }

        return Storage::disk('local')->download(
            $attachment->file_path,
            $attachment->file_name,
        );
    }
}
