<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Document;
use Inertia\Inertia;
use Inertia\Response;

class DocumentController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', Document::class);

        $user = request()->user();

        $documents = Document::query()
            ->with(['type:id,name'])
            ->latest()
            ->get()
            ->filter(fn (Document $document) => $user->can('view', $document))
            ->values();

        return Inertia::render('Portal/Documents/Index', [
            'documents' => [
                'data' => $documents,
            ],
        ]);
    }

    public function show(Document $document): Response
    {
        $this->authorize('view', $document);

        $document->load(['type:id,name', 'related']);

        return Inertia::render('Portal/Documents/Show', [
            'document' => $document,
            'can' => [
                'download' => request()->user()->can('download', $document),
            ],
        ]);
    }
}
