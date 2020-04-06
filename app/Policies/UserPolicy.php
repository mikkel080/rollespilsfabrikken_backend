<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    public function ban(User $user) {
        return $user->isSuperUser();
    }

    public function unban(User $user) {
        return $user->isSuperUser();
    }

    public function delete(User $user, User $userTarget) {
        if ($user->isSuperUser()) {
            return true;
        } else {
            return $userTarget['id'] === $user['id'];
        }
    }

    public function changeUsername(User $user, User $userTarget) {
        return $userTarget['id'] === $user['id'];
    }
}
