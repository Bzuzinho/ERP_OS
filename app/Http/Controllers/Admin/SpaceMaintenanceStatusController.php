<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Spaces\UpdateSpaceMaintenanceStatusAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Spaces\UpdateSpaceMaintenanceStatusRequest;
use App\Models\SpaceMaintenanceRecord;
use Illuminate\Http\RedirectResponse;

class SpaceMaintenanceStatusController extends Controller
{
    public function update(UpdateSpaceMaintenanceStatusRequest $request, SpaceMaintenanceRecord $spaceMaintenance, UpdateSpaceMaintenanceStatusAction $action): RedirectResponse
    {
        $action->execute($spaceMaintenance, $request->validated('status'), $request->user());

        return back()->with('success', 'Estado da manutencao atualizado com sucesso.');
    }
}
