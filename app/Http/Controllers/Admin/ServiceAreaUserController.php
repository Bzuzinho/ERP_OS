<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ServiceAreas\StoreServiceAreaUserRequest;
use App\Models\ServiceArea;
use Illuminate\Http\RedirectResponse;

class ServiceAreaUserController extends Controller
{
    public function store(StoreServiceAreaUserRequest $request, ServiceArea $serviceArea): RedirectResponse
    {
        $this->authorize('manageUsers', $serviceArea);

        $data = $request->validated();

        $serviceArea->users()->syncWithoutDetaching([
            (int) $data['user_id'] => [
                'role' => $data['role'] ?? null,
                'is_primary' => $data['is_primary'] ?? false,
            ],
        ]);

        return back()->with('success', 'Utilizador associado a area funcional.');
    }

    public function destroy(ServiceArea $serviceArea, int $userId): RedirectResponse
    {
        $this->authorize('manageUsers', $serviceArea);

        $serviceArea->users()->detach($userId);

        return back()->with('success', 'Utilizador removido da area funcional.');
    }
}
