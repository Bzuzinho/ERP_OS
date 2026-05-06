<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Actions\Settings\CreateUserAction;
use App\Actions\Settings\UpdateUserAction;
use App\Actions\Settings\UpdateUserRolesAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\StoreUserRequest;
use App\Http\Requests\Settings\UpdateUserRequest;
use App\Http\Requests\Settings\UpdateUserRolesRequest;
use App\Http\Requests\Settings\UpdateUserAvatarRequest;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', User::class);

        $search = $request->string('search')->toString();
        $status = $request->string('status')->toString(); // active|inactive|''
        $type   = $request->string('type')->toString();   // internal|portal|''

        $internalRoles = ['super_admin', 'admin_junta', 'executivo', 'administrativo', 'operacional', 'manutencao', 'armazem', 'rh'];
        $portalRoles   = ['cidadao', 'associacao', 'empresa'];

        $users = User::query()
            ->with(['organization:id,name', 'roles:id,name'])
            ->when($search, fn ($q) => $q
                ->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%"))
            ->when($status === 'active', fn ($q) => $q->where('is_active', true))
            ->when($status === 'inactive', fn ($q) => $q->where('is_active', false))
            ->when($type === 'internal', fn ($q) => $q->whereHas('roles', fn ($r) => $r->whereIn('name', $internalRoles)))
            ->when($type === 'portal', fn ($q) => $q->whereHas('roles', fn ($r) => $r->whereIn('name', $portalRoles)))
            ->orderBy('name')
            ->paginate(config('juntaos.default_pagination', 15))
            ->withQueryString();

        return Inertia::render('Admin/Settings/Users/Index', [
            'users'   => $users,
            'filters' => compact('search', 'status', 'type'),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', User::class);

        $organizations = Organization::orderBy('name')->get(['id', 'name']);
        $roles         = Role::orderBy('name')->get(['id', 'name']);

        return Inertia::render('Admin/Settings/Users/Create', [
            'organizations' => $organizations,
            'roles'         => $roles,
        ]);
    }

    public function store(StoreUserRequest $request, CreateUserAction $action): RedirectResponse
    {
        $this->authorize('create', User::class);

        $user = $action->execute($request->validated());

        return redirect()
            ->route('admin.settings.users.show', $user)
            ->with('success', 'Utilizador criado com sucesso.');
    }

    public function show(User $user): Response
    {
        $this->authorize('view', $user);

        $user->load(['organization:id,name', 'roles:id,name', 'roles.permissions:id,name']);

        $effectivePermissions = $user->getAllPermissions()
            ->pluck('name')
            ->sort()
            ->values();

        $canManageRoles     = request()->user()->can('manageRoles', $user);
        $canResetPassword   = request()->user()->can('resetPassword', $user);
        $canActivate        = request()->user()->can('activate', $user);
        $canDeactivate      = request()->user()->can('deactivate', $user);

        return Inertia::render('Admin/Settings/Users/Show', [
            'user'                 => $user,
            'effectivePermissions' => $effectivePermissions,
            'can' => [
                'update'        => request()->user()->can('update', $user),
                'manageRoles'   => $canManageRoles,
                'resetPassword' => $canResetPassword,
                'activate'      => $canActivate,
                'deactivate'    => $canDeactivate,
            ],
        ]);
    }

    public function edit(User $user): Response
    {
        $this->authorize('update', $user);

        $organizations = Organization::orderBy('name')->get(['id', 'name']);
        $roles         = Role::orderBy('name')->get(['id', 'name']);
        $user->load(['organization:id,name', 'roles:id,name']);

        $authUser = request()->user();
        $canManageRoles = $authUser->can('manageRoles', $user);

        return Inertia::render('Admin/Settings/Users/Edit', [
            'user'          => $user,
            'organizations' => $organizations,
            'roles'         => $roles,
            'canManageRoles' => $canManageRoles,
            'isSuperAdmin'  => $authUser->hasRole('super_admin'),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user, UpdateUserAction $action): RedirectResponse
    {
        $this->authorize('update', $user);

        $action->execute($user, $request->validated());

        return redirect()
            ->route('admin.settings.users.show', $user)
            ->with('success', 'Utilizador atualizado com sucesso.');
    }

    public function updateRoles(UpdateUserRolesRequest $request, User $user, UpdateUserRolesAction $action): RedirectResponse
    {
        $this->authorize('manageRoles', $user);

        $action->execute($request->user(), $user, $request->validated()['roles']);

        return redirect()
            ->route('admin.settings.users.show', $user)
            ->with('success', 'Perfis atualizados com sucesso.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->authorize('delete', $user);

        $user->update(['is_active' => false]);

        return redirect()
            ->route('admin.settings.users.index')
            ->with('success', 'Utilizador desativado com sucesso.');
    }

    public function updateAvatar(UpdateUserAvatarRequest $request, User $user, UpdateUserAction $action): RedirectResponse
    {
        $this->authorize('update', $user);

        $action->updateAvatar($user, $request->file('avatar'));

        return redirect()
            ->route('admin.settings.users.edit', $user)
            ->with('success', 'Foto de perfil atualizada.');
    }
}
