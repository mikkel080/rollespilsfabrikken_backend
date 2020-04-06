<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\Role\RoleCollection;
use App\Http\Resources\Role\RoleWithPermissions;
use App\Models\Calendar;
use App\Models\Forum;
use App\Models\Permission;
use App\Models\Role;
use App\Models\RolePerm;
use Illuminate\Http\Request;
use App\Http\Requests\API\Auth\RolePerm\Add;
use App\Http\Requests\API\Auth\RolePerm\CalendarAdd;
use App\Http\Requests\API\Auth\RolePerm\ForumAdd;
use App\Http\Requests\API\Auth\RolePerm\CalendarIndex;
use App\Http\Requests\API\Auth\RolePerm\ForumIndex;
use App\Http\Resources\Permission\PermissionWithRoles;
use App\Http\Resources\Permission\PermissionWithoutParent;
use App\Http\Resources\Permission\Permission as PermissionResource;
use App\Http\Resources\Permission\PermissionCollection;

class PermissionRoleController extends Controller
{
    public function calendarIndex(CalendarIndex $request, Role $role, Calendar $calendar) {
        return response()->json([
            'message' => 'success',
            'permissions_enabled' => PermissionWithoutParent::collection(
                $role
                    ->permissions()
                    ->where('obj_id', '=', $calendar['obj_id'])
                    ->get()
            ),
            'permissions_disabled' => PermissionWithoutParent::collection(
                $calendar
                    ->permissions()
                    ->whereNotIn('id', $role
                        ->permissions()
                        ->where('obj_id', '=', $calendar['obj_id'])
                        ->get()->pluck('id')
                    )
            )
        ]);
    }

    public function forumIndex(ForumIndex $request, Role $role, Forum $forum) {
        return response()->json([
            'message' => 'success',
            'permissions_enabled' => PermissionWithoutParent::collection(
                $role
                    ->permissions()
                    ->where('obj_id', '=', $forum['obj_id'])
                    ->get()
            ),
            'permissions_disabled' => PermissionWithoutParent::collection(
                $forum
                    ->permissions()
                    ->whereNotIn('id', $role
                            ->permissions()
                            ->where('obj_id', '=', $forum['obj_id'])
                            ->get()->pluck('id')
                    )
            )
        ]);
    }

    public function permissionAdd(Add $request, Permission $permission, Role $role) {
        $rolePerm = (new RolePerm)
            ->where([
                ['permission_id', '=', $permission['id']],
                ['role_id', '=', $role['id']]
            ])->first();

        if ($rolePerm === null) {
            $rolePerm = (new RolePerm);
            $rolePerm->role()->associate($role);
            $rolePerm->permission()->associate($permission);

            $rolePerm->save();
        }

        return response()->json([
            'message' => 'success',
            'role' => new RoleWithPermissions($role)
        ]);
    }

    public function roleAdd(Add $request, Role $role, Permission $permission) {
        $rolePerm = (new RolePerm)
            ->where([
                ['permission_id', '=', $permission['id']],
                ['role_id', '=', $role['id']]
            ])->first();

        if ($rolePerm === null) {
            $rolePerm = (new RolePerm);
            $rolePerm->role()->associate($role);
            $rolePerm->permission()->associate($permission);

            $rolePerm->save();
        }

        return response()->json([
            'message' => 'success',
            'role' => new RoleWithPermissions($role)
        ]);
    }

    public function calendarAdd(CalendarAdd $request, Calendar $calendar, $level, Role $role) {
        $permission = $calendar
            ->permissions()
            ->where('level', '=', $level)
            ->first();

        $rolePerm = (new RolePerm)
            ->where([
                ['permission_id', '=', $permission['id']],
                ['role_id', '=', $role['id']]
            ])->first();

        if ($rolePerm === null) {
            $rolePerm = (new RolePerm);
            $rolePerm->role()->associate($role);
            $rolePerm->permission()->associate($permission);

            $rolePerm->save();
        }

        return response()->json([
            'message' => 'success',
            'role' => new RoleWithPermissions($role)
        ]);
    }

    public function forumAdd(ForumAdd $request, Forum $forum, $level, Role $role) {
        $permission = $forum
            ->permissions()
            ->where('level', '=', $level)
            ->first();

        $rolePerm = (new RolePerm)
            ->where([
                ['permission_id', '=', $permission['id']],
                ['role_id', '=', $role['id']]
            ])->first();

        if ($rolePerm === null) {
            $rolePerm = (new RolePerm);
            $rolePerm
                ->role()
                ->associate($role);
            $rolePerm
                ->permission()
                ->associate($permission);

            $rolePerm->save();
        }

        return response()->json([
            'message' => 'success',
            'role' => new RoleWithPermissions($role)
        ]);
    }

    public function permissionDelete(Add $request, Permission $permission, Role $role) {
        (new RolePerm)
            ->where([
                ['permission_id', '=', $permission['id']],
                ['role_id', '=', $role['id']]
            ])->get()
            ->each(function (RolePerm $role, $key) {
               $role->delete();
               return true;
            });

        return response()->json([
            'message' => 'success'
        ]);
    }

    public function roleDelete(Add $request, Role $role, Permission $permission) {
        (new RolePerm)
            ->where([
                ['permission_id', '=', $permission['id']],
                ['role_id', '=', $role['id']]
            ])->get()
            ->each(function (RolePerm $role, $key) {
                $role->delete();
                return true;
            });

        return response()->json([
            'message' => 'success'
        ]);
    }

    public function calendarDelete(CalendarAdd $request, Calendar $calendar, $level, Role $role) {
        $permission = $calendar
            ->permissions()
            ->where('level', '=', $level)
            ->first();

        (new RolePerm)
            ->where([
                ['permission_id', '=', $permission['id']],
                ['role_id', '=', $role['id']]
            ])->get()
            ->each(function (RolePerm $role, $key) {
                $role->delete();
                return true;
            });

        return response()->json([
            'message' => 'success'
        ]);
    }

    public function forumDelete(ForumAdd $request, Forum $forum, $level, Role $role) {
        $permission = $forum
            ->permissions()
            ->where('level', '=', $level)
            ->first();

        (new RolePerm)
            ->where([
                ['permission_id', '=', $permission['id']],
                ['role_id', '=', $role['id']]
            ])->get()
            ->each(function (RolePerm $role, $key) {
                $role->delete();
                return true;
            });

        return response()->json([
            'message' => 'success'
        ]);
    }
}
