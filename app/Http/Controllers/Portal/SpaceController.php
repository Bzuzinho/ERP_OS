<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Space;
use App\Support\OrganizationScope;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SpaceController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Space::class);

        $spaces = Space::query()
            ->visibleToUser($request->user())
            ->where('is_active', true)
            ->where(function ($query) {
                $query->where('is_public', true)
                    ->orWhere('status', 'available');
            })
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('Portal/Spaces/Index', [
            'spaces' => $spaces,
        ]);
    }

    public function show(Space $space): Response
    {
        OrganizationScope::ensureModelBelongsToUserOrganization($space, request()->user());
        $this->authorize('view', $space);

        return Inertia::render('Portal/Spaces/Show', [
            'space' => $space->only([
                'id',
                'name',
                'description',
                'location_text',
                'capacity',
                'status',
                'rules',
            ]),
        ]);
    }
}
