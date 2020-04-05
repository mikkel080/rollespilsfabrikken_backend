<?php

namespace App\Observers;

use App\Models\Role;
use App\Models\RolePerm;
use App\Models\UserRole;

class RoleObserver
{
    /**
     * Handle the role "created" event.
     *
     * @param  \App\Models\Role  $role
     * @return void
     */
    public function created(Role $role)
    {
        //
    }

    /**
     * Handle the role "updated" event.
     *
     * @param  \App\Models\Role  $role
     * @return void
     */
    public function updated(Role $role)
    {
        //
    }

    /**
     * Handle the role "deleted" event.
     *
     * @param  \App\Models\Role  $role
     * @return void
     */
    public function deleted(Role $role)
    {
        (new UserRole)
            ->where('role_id', '=', $role['id'])
            ->get()
            ->each(function (UserRole $userRole, $item) {
                $userRole->delete();
            });

        (new RolePerm)
            ->where('role_id', '=', $role['id'])
            ->get()
            ->each(function (RolePerm $rolePerm, $item) {
                $rolePerm->delete();
            });
    }

    /**
     * Handle the role "restored" event.
     *
     * @param  \App\Models\Role  $role
     * @return void
     */
    public function restored(Role $role)
    {
        //
    }

    /**
     * Handle the role "force deleted" event.
     *
     * @param  \App\Models\Role  $role
     * @return void
     */
    public function forceDeleted(Role $role)
    {
        //
    }
}
