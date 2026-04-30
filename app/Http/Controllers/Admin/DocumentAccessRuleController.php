<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Documents\GrantDocumentAccessAction;
use App\Actions\Documents\RevokeDocumentAccessAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Documents\StoreDocumentAccessRuleRequest;
use App\Models\Document;
use App\Models\DocumentAccessRule;
use Illuminate\Http\RedirectResponse;

class DocumentAccessRuleController extends Controller
{
    public function store(StoreDocumentAccessRuleRequest $request, Document $document, GrantDocumentAccessAction $grantDocumentAccessAction): RedirectResponse
    {
        $this->authorize('manageAccess', $document);

        $grantDocumentAccessAction->execute($document, $request->user(), [
            ...$request->safe()->except('document_id'),
        ]);

        return to_route('admin.documents.show', $document)->with('success', 'Regra de acesso criada com sucesso.');
    }

    public function destroy(Document $document, DocumentAccessRule $documentAccessRule, RevokeDocumentAccessAction $revokeDocumentAccessAction): RedirectResponse
    {
        $this->authorize('delete', $documentAccessRule);

        if ((int) $documentAccessRule->document_id !== (int) $document->id) {
            abort(404);
        }

        $revokeDocumentAccessAction->execute($documentAccessRule, request()->user());

        return to_route('admin.documents.show', $document)->with('success', 'Regra de acesso removida com sucesso.');
    }
}
