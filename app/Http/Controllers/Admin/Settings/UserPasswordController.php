<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Actions\Settings\ResetUserPasswordAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ResetUserPasswordRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

class UserPasswordController extends Controller
{
    public function reset(ResetUserPasswordRequest $request, User $user, ResetUserPasswordAction $action): RedirectResponse
    {
        $this->authorize('resetPassword', $user);

        $action->execute($user, $request->validated()['password']);

        return back()->with('success', 'Password redefinida com sucesso.');
    }
}
