<?php

namespace App\Policies;

use App\Models\Permission;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PermissionPolicy
{
    use HandlesAuthorization;

    public function before(User $user, $ability)
    {
        if ($user->isSuperUser()) {
            return true;
        }
    }

    /**
     * Determine whether the user can view any permissions.
     *
     * @param User $user
     * @return bool
     */
    public function viewAny(User $user)
    {
        return $user->isSuperUser();
    }

    /**
     * Determine whether the user can view the permission.
     *
     * @param User $user
     * @param Permission $permission
     * @return bool
     */
    public function view(User $user, Permission $permission)
    {
        return $user->isSuperUser();
    }

    /**
     * Determine whether the user can view the permissions linked to the forum.
     *
     * @param User $user
     * @return bool
     */
    public function viewAnyForum(User $user)
    {
        return $user->isSuperUser();
    }

    /**
     * Determine whether the user can view the permissions linked to the calendar.
     *
     * @param User $user
     * @return bool
     */
    public function viewAnyCalendar(User $user)
    {
        return $user->isSuperUser();
    }
}
