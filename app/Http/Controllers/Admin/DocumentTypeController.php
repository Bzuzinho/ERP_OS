<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Documents\StoreDocumentTypeRequest;
use App\Http\Requests\Documents\UpdateDocumentTypeRequest;
use App\Models\DocumentType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DocumentTypeController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', DocumentType::class);

        $search = $request->string('search')->toString();

        $documentTypes = DocumentType::query()
            ->when($search, fn ($query) => $query
                ->where('name', 'like', "%{$search}%")
                ->orWhere('slug', 'like', "%{$search}%"))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/DocumentTypes/Index', [
            'documentTypes' => $documentTypes,
            'filters' => compact('search'),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', DocumentType::class);

        return Inertia::render('Admin/DocumentTypes/Create');
    }

    public function store(StoreDocumentTypeRequest $request): RedirectResponse
    {
        DocumentType::query()->create([
            ...$request->validated(),
            'organization_id' => $request->user()->organization_id,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return to_route('admin.document-types.index')->with('success', 'Tipo de documento criado com sucesso.');
    }

    public function edit(DocumentType $documentType): Response
    {
        $this->authorize('update', $documentType);

        return Inertia::render('Admin/DocumentTypes/Edit', [
            'documentType' => $documentType,
        ]);
    }

    public function update(UpdateDocumentTypeRequest $request, DocumentType $documentType): RedirectResponse
    {
        $documentType->update($request->validated());

        return to_route('admin.document-types.index')->with('success', 'Tipo de documento atualizado com sucesso.');
    }

    public function destroy(DocumentType $documentType): RedirectResponse
    {
        $this->authorize('delete', $documentType);

        $documentType->delete();

        return to_route('admin.document-types.index')->with('success', 'Tipo de documento removido com sucesso.');
    }
}
