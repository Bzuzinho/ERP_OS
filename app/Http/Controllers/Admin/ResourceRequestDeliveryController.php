<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Inventory\DeliverResourceRequestAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\DeliverResourceRequestRequest;
use App\Models\ResourceRequest;
use Illuminate\Http\RedirectResponse;

class ResourceRequestDeliveryController extends Controller
{
    public function deliver(DeliverResourceRequestRequest $request, ResourceRequest $resourceRequest, DeliverResourceRequestAction $action): RedirectResponse
    {
        abort_if($resourceRequest->organization_id !== $request->user()->organization_id, 403);

        try {
            $action->execute($request->user(), $resourceRequest, $request->validated());
        } catch (\RuntimeException $exception) {
            return back()->withErrors(['resource_request' => $exception->getMessage()]);
        }

        return back()->with('success', 'Material entregue com sucesso.');
    }
}
