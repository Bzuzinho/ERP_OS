<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Documents\ArchiveDocumentAction;
use App\Actions\Documents\CreateDocumentAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Documents\StoreDocumentRequest;
use App\Http\Requests\Documents\UpdateDocumentRequest;
use App\Models\Contact;
use App\Models\Document;
use App\Models\DocumentType;
use App\Models\Event;
use App\Models\Task;
use App\Models\Ticket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DocumentController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Document::class);

        $search = $request->string('search')->toString();
        $documentTypeId = $request->string('document_type_id')->toString();
        $visibility = $request->string('visibility')->toString();
        $status = $request->string('status')->toString();
        $relatedType = $request->string('related_type')->toString();

        $documents = Document::query()
            ->with(['type:id,name', 'uploader:id,name'])
            ->when($search, fn ($query) => $query->where('title', 'like', "%{$search}%"))
            ->when($documentTypeId, fn ($query) => $query->where('document_type_id', $documentTypeId))
            ->when($visibility, fn ($query) => $query->where('visibility', $visibility))
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($relatedType, fn ($query) => $query->where('related_type', $relatedType))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/Documents/Index', [
            'documents' => $documents,
            'filters' => compact('search', 'documentTypeId', 'visibility', 'status', 'relatedType'),
            'documentTypes' => DocumentType::query()->select('id', 'name')->orderBy('name')->get(),
            'visibilities' => Document::VISIBILITIES,
            'statuses' => Document::STATUSES,
            'relatedTypes' => [Ticket::class, Contact::class, Task::class, Event::class],
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Document::class);

        return Inertia::render('Admin/Documents/Create', [
            'documentTypes' => DocumentType::query()->select('id', 'name')->orderBy('name')->get(),
            'visibilities' => Document::VISIBILITIES,
            'statuses' => Document::STATUSES,
            'relatedEntities' => $this->relatedEntities(),
        ]);
    }

    public function store(StoreDocumentRequest $request, CreateDocumentAction $createDocumentAction): RedirectResponse
    {
        $document = $createDocumentAction->execute($request->user(), $request->validated(), $request->file('file'));

        return to_route('admin.documents.show', $document)->with('success', 'Documento criado com sucesso.');
    }

    public function show(Document $document): Response
    {
        $this->authorize('view', $document);

        $document->load([
            'type:id,name',
            'uploader:id,name',
            'versions.uploader:id,name',
            'accessRules.user:id,name',
            'accessRules.contact:id,name',
            'accessRules.creator:id,name',
            'comments.user:id,name',
            'activityLogs.user:id,name',
            'meetingMinute.event:id,title,start_at',
            'related',
        ]);

        return Inertia::render('Admin/Documents/Show', [
            'document' => $document,
            'can' => [
                'download' => request()->user()->can('download', $document),
                'manageAccess' => request()->user()->can('manageAccess', $document),
                'update' => request()->user()->can('update', $document),
            ],
        ]);
    }

    public function edit(Document $document): Response
    {
        $this->authorize('update', $document);

        return Inertia::render('Admin/Documents/Edit', [
            'document' => $document,
            'documentTypes' => DocumentType::query()->select('id', 'name')->orderBy('name')->get(),
            'visibilities' => Document::VISIBILITIES,
            'statuses' => Document::STATUSES,
            'relatedEntities' => $this->relatedEntities(),
        ]);
    }

    public function update(UpdateDocumentRequest $request, Document $document): RedirectResponse
    {
        $document->update($request->validated());

        return to_route('admin.documents.show', $document)->with('success', 'Documento atualizado com sucesso.');
    }

    public function destroy(Document $document, ArchiveDocumentAction $archiveDocumentAction): RedirectResponse
    {
        $this->authorize('delete', $document);

        $archiveDocumentAction->execute($document, request()->user());

        return to_route('admin.documents.index')->with('success', 'Documento arquivado com sucesso.');
    }

    private function relatedEntities(): array
    {
        return [
            'tickets' => Ticket::query()->select('id', 'reference', 'title')->latest()->limit(100)->get(),
            'contacts' => Contact::query()->select('id', 'name')->orderBy('name')->limit(100)->get(),
            'tasks' => Task::query()->select('id', 'title')->latest()->limit(100)->get(),
            'events' => Event::query()->select('id', 'title')->latest('start_at')->limit(100)->get(),
            'types' => [
                ['label' => 'Ticket', 'value' => Ticket::class],
                ['label' => 'Contacto', 'value' => Contact::class],
                ['label' => 'Tarefa', 'value' => Task::class],
                ['label' => 'Evento', 'value' => Event::class],
            ],
        ];
    }
}
