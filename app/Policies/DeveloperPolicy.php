<?php

namespace App\Policies;

use App\Models\Developer;
use App\Models\User;

class DeveloperPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Developer $developer): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Developer $developer): bool
    {
        return true;
    }

    public function delete(User $user, Developer $developer): bool
    {
        return true;
    }
}
