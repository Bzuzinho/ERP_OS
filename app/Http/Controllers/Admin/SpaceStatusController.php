<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Spaces\UpdateSpaceStatusAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Spaces\UpdateSpaceStatusRequest;
use App\Models\Space;
use Illuminate\Http\RedirectResponse;

class SpaceStatusController extends Controller
{
    public function update(UpdateSpaceStatusRequest $request, Space $space, UpdateSpaceStatusAction $action): RedirectResponse
    {
        $action->execute($space, $request->validated('status'), $request->user());

        return back()->with('success', 'Estado do espaco atualizado com sucesso.');
    }
}
