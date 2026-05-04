<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\UpdateRoleRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', Role::class);

        $roles = Role::withCount('users', 'permissions')
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('Admin/Settings/Roles/Index', [
            'roles' => $roles,
        ]);
    }

    public function show(Role $role): Response
    {
        $this->authorize('view', $role);

        $role->load('permissions:id,name');

        $allPermissions = Permission::orderBy('name')->get(['id', 'name']);

        return Inertia::render('Admin/Settings/Roles/Show', [
            'role'           => $role,
            'allPermissions' => $allPermissions,
            'canEdit'        => request()->user()->can('update', $role),
        ]);
    }

    public function edit(Role $role): Response
    {
        $this->authorize('update', $role);

        $role->load('permissions:id,name');
        $allPermissions = Permission::orderBy('name')->get(['id', 'name']);

        return Inertia::render('Admin/Settings/Roles/Edit', [
            'role'           => $role,
            'allPermissions' => $allPermissions,
        ]);
    }

    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        $this->authorize('update', $role);

        $role->syncPermissions($request->validated()['permissions']);

        return redirect()
            ->route('admin.settings.roles.show', $role)
            ->with('success', 'Permissões do perfil atualizadas.');
    }
}
