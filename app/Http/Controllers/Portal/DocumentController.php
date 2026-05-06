<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Support\OrganizationScope;
use Inertia\Inertia;
use Inertia\Response;

class DocumentController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', Document::class);

        $user = request()->user();

        $documents = Document::query()
            ->visibleToUser($user)
            ->with(['type:id,name'])
            ->latest()
            ->get()
            ->filter(fn (Document $document) => $user->can('view', $document))
            ->values()
            ->map(fn (Document $document) => [
                'id' => $document->id,
                'title' => $document->title,
                'created_at' => $document->created_at,
                'type' => $document->type,
                'can_download' => $user->can('download', $document),
            ]);

        return Inertia::render('Portal/Documents/Index', [
            'documents' => [
                'data' => $documents,
            ],
        ]);
    }

    public function show(Document $document): Response
    {
        OrganizationScope::ensureModelBelongsToUserOrganization($document, request()->user());
        $this->authorize('view', $document);

        $document->load(['type:id,name']);

        return Inertia::render('Portal/Documents/Show', [
            'document' => [
                'id' => $document->id,
                'title' => $document->title,
                'description' => $document->description,
                'current_version' => $document->current_version,
                'original_name' => $document->original_name,
                'file_name' => $document->file_name,
                'created_at' => $document->created_at,
                'type' => $document->type,
            ],
            'can' => [
                'download' => request()->user()->can('download', $document),
            ],
        ]);
    }
}
