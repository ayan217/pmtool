<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    public function view(User $user, Task $task): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Task $task): bool
    {
        return true;
    }

    public function delete(User $user, Task $task): bool
    {
        return true;
    }

    public function restore(User $user, Task $task): bool
    {
        return true;
    }

    public function complete(User $user, Task $task): bool
    {
        return true;
    }

    public function archive(User $user, Task $task): bool
    {
        return true;
    }

    public function remind(User $user, Task $task): bool
    {
        return true;
    }
}
