<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Support\OrganizationScope;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentDownloadController extends Controller
{
    public function __invoke(Request $request, Document $document): StreamedResponse
    {
        OrganizationScope::ensureModelBelongsToUserOrganization($document, $request->user());
        $this->authorize('download', $document);

        return Storage::disk('local')->download($document->file_path, $document->original_name ?: $document->file_name);
    }
}
