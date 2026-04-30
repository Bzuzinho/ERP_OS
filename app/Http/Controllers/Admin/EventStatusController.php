<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Events\UpdateEventStatusAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Events\UpdateEventStatusRequest;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;

class EventStatusController extends Controller
{
    public function update(UpdateEventStatusRequest $request, Event $event, UpdateEventStatusAction $updateEventStatusAction): RedirectResponse
    {
        $updateEventStatusAction->execute($event, $request->validated('status'), $request->user());

        return back()->with('success', 'Estado do evento atualizado com sucesso.');
    }
}
