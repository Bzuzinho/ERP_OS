<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttachmentDownloadController extends Controller
{
    public function __invoke(Request $request, Attachment $attachment): StreamedResponse
    {
        // Only users who can view the parent resource may download
        $attachable = $attachment->attachable;
        if ($attachable) {
            $this->authorize('view', $attachable);
        } else {
            abort(404);
        }

        abort_unless(Storage::disk('local')->exists($attachment->file_path), 404);

        return Storage::disk('local')->download(
            $attachment->file_path,
            $attachment->file_name,
        );
    }
}
