<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->hasRole('super_admin') ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->can('tasks.view');
    }

    public function view(User $user, Task $task): bool
    {
        if ($user->can('tasks.view')) {
            return true;
        }

        return $task->assigned_to === $user->id || $task->created_by === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->can('tasks.create');
    }

    public function update(User $user, Task $task): bool
    {
        return $user->can('tasks.update');
    }

    public function assign(User $user, Task $task): bool
    {
        return $user->can('tasks.assign');
    }

    public function complete(User $user, Task $task): bool
    {
        return $user->can('tasks.complete');
    }

    public function delete(User $user, Task $task): bool
    {
        return $user->can('tasks.delete');
    }
}
