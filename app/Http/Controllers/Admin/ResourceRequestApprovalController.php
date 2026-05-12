<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Inventory\ApproveResourceRequestAction;
use App\Actions\Inventory\PrepareResourceRequestAction;
use App\Actions\Inventory\RejectResourceRequestAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\ApproveResourceRequestRequest;
use App\Http\Requests\Inventory\RejectResourceRequestRequest;
use App\Models\ResourceRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ResourceRequestApprovalController extends Controller
{
    public function approve(ApproveResourceRequestRequest $request, ResourceRequest $resourceRequest, ApproveResourceRequestAction $action): RedirectResponse
    {
        abort_if($resourceRequest->organization_id !== $request->user()->organization_id, 403);

        try {
            $action->execute($request->user(), $resourceRequest, $request->validated());
        } catch (\RuntimeException $exception) {
            return back()->withErrors(['resource_request' => $exception->getMessage()]);
        }

        return back()->with('success', 'Requisicao aprovada com sucesso.');
    }

    public function reject(RejectResourceRequestRequest $request, ResourceRequest $resourceRequest, RejectResourceRequestAction $action): RedirectResponse
    {
        abort_if($resourceRequest->organization_id !== $request->user()->organization_id, 403);

        try {
            $action->execute($request->user(), $resourceRequest, $request->validated());
        } catch (\RuntimeException $exception) {
            return back()->withErrors(['resource_request' => $exception->getMessage()]);
        }

        return back()->with('success', 'Requisicao rejeitada com sucesso.');
    }

    public function prepare(Request $request, ResourceRequest $resourceRequest, PrepareResourceRequestAction $action): RedirectResponse
    {
        abort_unless($request->user()->can('resources.manage'), 403);
        abort_if($resourceRequest->organization_id !== $request->user()->organization_id, 403);

        try {
            $action->execute($request->user(), $resourceRequest);
        } catch (\RuntimeException $exception) {
            return back()->withErrors(['resource_request' => $exception->getMessage()]);
        }

        return back()->with('success', 'Requisicao preparada com sucesso.');
    }
}
