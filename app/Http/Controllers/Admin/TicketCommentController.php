<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Comments\StoreCommentRequest;
use App\Models\Ticket;
use Illuminate\Http\RedirectResponse;

class TicketCommentController extends Controller
{
    public function store(StoreCommentRequest $request, Ticket $ticket): RedirectResponse
    {
        $this->authorize('view', $ticket);

        $data = $request->validated();

        $ticket->comments()->create([
            'organization_id' => $ticket->organization_id,
            'user_id' => $request->user()->id,
            'body' => $data['body'],
            'visibility' => $data['visibility'] ?? 'internal',
        ]);

        return back()->with('success', 'Comentario adicionado com sucesso.');
    }
}
