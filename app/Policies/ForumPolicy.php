<?php

namespace App\Policies;

use App\Models\Forum;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ForumPolicy
{
    use HandlesAuthorization;

    public function before(User $user, $ability)
    {
        if ($user->isSuperUser()) {
            return true;
        }
    }

    /**
     * Determine whether the user can view any forums.
     *
     * @param User $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can view the forum.
     *
     * @param User $user
     * @param Forum $forum
     * @return mixed
     */
    public function view(User $user, Forum $forum)
    {
        return (new PolicyHelper())->getLevel($user,  $forum['obj_id'], 2);
    }

    /**
     * Determine whether the user can create forums.
     *
     * @param User $user
     * @return mixed
     */
    public function create(User $user)
    {
        return $user->isSuperUser();
    }

    /**
     * Determine whether the user can update the forum.
     *
     * @param User $user
     * @param Forum $forum
     * @return mixed
     */
    public function update(User $user, Forum $forum)
    {
        return $user->isSuperUser();
    }

    /**
     * Determine whether the user can delete the forum.
     *
     * @param User $user
     * @param Forum $forum
     * @return mixed
     */
    public function delete(User $user, Forum $forum)
    {
        return $user->isSuperUser();
    }

    /**
     * Determine whether the user can restore the forum.
     *
     * @param User $user
     * @param Forum $forum
     * @return mixed
     */
    public function restore(User $user, Forum $forum)
    {
        return $user->isSuperUser();
    }

    /**
     * Determine whether the user can permanently delete the forum.
     *
     * @param User $user
     * @param Forum $forum
     * @return mixed
     */
    public function forceDelete(User $user, Forum $forum)
    {
        return $user->isSuperUser();
    }
}
