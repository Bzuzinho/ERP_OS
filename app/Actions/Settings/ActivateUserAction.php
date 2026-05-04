<?php

namespace App\Actions\Settings;

use App\Models\User;
use Illuminate\Validation\ValidationException;

class ActivateUserAction
{
    public function execute(User $target): User
    {
        $target->update(['is_active' => true]);
        return $target->fresh();
    }
}
