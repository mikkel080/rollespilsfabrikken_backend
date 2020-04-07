<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user) {
        return $user->isSuperUser();
    }

    public function ban(User $user, User $userTarget) {
        return $user->isSuperUser();
    }

    public function unban(User $user, User $userTarget) {
        return $user->isSuperUser();
    }

    public function destroy(User $user, User $userTarget) {
        if ($user->isSuperUser()) {
            return true;
        } else {
            return $userTarget['id'] === $user['id'];
        }
    }

    public function reset(User $user, User $userTarget) {
        return $user->isSuperUser();
    }

    public function clear(User $user, User $userTarget) {
        return $user->isSuperUser();
    }

    public function changeUsername(User $user, User $userTarget) {
        return $userTarget['id'] === $user['id'];
    }

    public function op(User $user, User $userTarget) {
        return $user->isSuperUser();
    }

    public function deop(User $user, User $userTarget) {
        return $user->isSuperUser();
    }
}
