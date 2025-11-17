<?php

namespace App\Policies;

use App\Models\Stage;
use App\Models\User;

class StagePolicy
{
    public function view(User $user, Stage $stage): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['hr','ceo']);
    }

    public function update(User $user, Stage $stage): bool
    {
        return in_array($user->role, ['hr','ceo']);
    }

    public function delete(User $user, Stage $stage): bool
    {
        return in_array($user->role, ['hr','ceo']);
    }
}
