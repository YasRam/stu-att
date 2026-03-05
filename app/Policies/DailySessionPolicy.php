<?php

namespace App\Policies;

use App\Models\DailySession;
use App\Models\User;

class DailySessionPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, DailySession $session): bool
    {
        return $user->isAdmin() || $session->teacher_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, DailySession $session): bool
    {
        return $user->isAdmin() || $session->teacher_id === $user->id;
    }

    public function delete(User $user, DailySession $session): bool
    {
        return $user->isAdmin() || $session->teacher_id === $user->id;
    }
}
