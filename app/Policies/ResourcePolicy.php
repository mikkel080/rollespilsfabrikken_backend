<?php

namespace App\Policies;

use App\Models\Resource;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ResourcePolicy
{
    use HandlesAuthorization;

    public function before(User $user, $ability)
    {
        if ($user->isSuperUser()) {
            return true;
        }
    }

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return auth()->check();
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Resource  $resource
     * @return mixed
     */
    public function view(User $user, Resource $resource)
    {
        return auth()->check();
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return auth()->user()->isSuperUser();
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Resource  $resource
     * @return mixed
     */
    public function update(User $user, Resource $resource)
    {
        return auth()->user()->isSuperUser();
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Resource  $resource
     * @return mixed
     */
    public function delete(User $user, Resource $resource)
    {
        return auth()->user()->isSuperUser();
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Resource  $resource
     * @return mixed
     */
    public function restore(User $user, Resource $resource)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Resource  $resource
     * @return mixed
     */
    public function forceDelete(User $user, Resource $resource)
    {
        //
    }
}
