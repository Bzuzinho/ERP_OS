<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Actions\Settings\ActivateUserAction;
use App\Actions\Settings\DeactivateUserAction;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

class UserStatusController extends Controller
{
    public function activate(User $user, ActivateUserAction $action): RedirectResponse
    {
        $this->authorize('activate', $user);

        $action->execute($user);

        return back()->with('success', 'Utilizador ativado com sucesso.');
    }

    public function deactivate(User $user, DeactivateUserAction $action): RedirectResponse
    {
        $this->authorize('deactivate', $user);

        $action->execute(request()->user(), $user);

        return back()->with('success', 'Utilizador desativado com sucesso.');
    }
}
