<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Inventory\ReturnResourceRequestAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\ReturnResourceRequestRequest;
use App\Models\ResourceRequest;
use Illuminate\Http\RedirectResponse;

class ResourceRequestReturnController extends Controller
{
    public function store(ReturnResourceRequestRequest $request, ResourceRequest $resourceRequest, ReturnResourceRequestAction $action): RedirectResponse
    {
        abort_if($resourceRequest->organization_id !== $request->user()->organization_id, 403);

        try {
            $action->execute($request->user(), $resourceRequest, $request->validated());
        } catch (\RuntimeException $exception) {
            return back()->withErrors(['resource_request' => $exception->getMessage()]);
        }

        return back()->with('success', 'Devolucao registada com sucesso.');
    }
}
