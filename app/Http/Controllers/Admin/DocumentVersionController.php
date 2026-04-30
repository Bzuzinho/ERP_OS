<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Documents\CreateDocumentVersionAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Documents\StoreDocumentVersionRequest;
use App\Models\Document;
use Illuminate\Http\RedirectResponse;

class DocumentVersionController extends Controller
{
    public function store(StoreDocumentVersionRequest $request, Document $document, CreateDocumentVersionAction $createDocumentVersionAction): RedirectResponse
    {
        $this->authorize('update', $document);

        $createDocumentVersionAction->execute(
            document: $document,
            user: $request->user(),
            file: $request->file('file'),
            notes: $request->validated('notes'),
        );

        return to_route('admin.documents.show', $document)->with('success', 'Nova versao do documento criada.');
    }
}
