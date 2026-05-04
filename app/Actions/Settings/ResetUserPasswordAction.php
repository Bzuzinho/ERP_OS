<?php

namespace App\Actions\Settings;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ResetUserPasswordAction
{
    public function execute(User $target, string $password): User
    {
        $target->update(['password' => Hash::make($password)]);
        return $target->fresh();
    }
}
