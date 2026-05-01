<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Http\Requests\Attachments\StoreAttachmentRequest;
use App\Models\Ticket;
use Illuminate\Http\RedirectResponse;

class TicketAttachmentController extends Controller
{
    public function store(StoreAttachmentRequest $request, Ticket $ticket): RedirectResponse
    {
        $this->authorize('view', $ticket);

        $file = $request->file('file');
        $path = $file->store('tickets/attachments', 'local');

        $ticket->attachments()->create([
            'organization_id' => $ticket->organization_id,
            'uploaded_by' => $request->user()->id,
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'visibility' => 'public',
        ]);

        return back()->with('success', 'Anexo enviado com sucesso.');
    }
}
