<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Spaces\CompleteSpaceCleaningRecordAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Spaces\CompleteSpaceCleaningRecordRequest;
use App\Models\SpaceCleaningRecord;
use Illuminate\Http\RedirectResponse;

class SpaceCleaningStatusController extends Controller
{
    public function complete(CompleteSpaceCleaningRecordRequest $request, SpaceCleaningRecord $spaceCleaning, CompleteSpaceCleaningRecordAction $action): RedirectResponse
    {
        $action->execute($spaceCleaning, $request->user());

        return back()->with('success', 'Limpeza concluida com sucesso.');
    }
}
