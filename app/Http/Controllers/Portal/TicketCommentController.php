<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Http\Requests\Comments\StoreCommentRequest;
use App\Models\Ticket;
use Illuminate\Http\RedirectResponse;

class TicketCommentController extends Controller
{
    public function store(StoreCommentRequest $request, Ticket $ticket): RedirectResponse
    {
        $this->authorize('view', $ticket);

        $ticket->comments()->create([
            'organization_id' => $ticket->organization_id,
            'user_id' => $request->user()->id,
            'body' => $request->validated('body'),
            'visibility' => 'public',
        ]);

        return back()->with('success', 'Comentario enviado com sucesso.');
    }
}
