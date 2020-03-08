<?php


namespace App\Policies;

use App\Models\User;

class PolicyHelper
{
    public function getLevel(User $user, $objectId) {
        $level = collect($user->permissions())
            ->where('obj_id', '=', $objectId)
            ->pluck('level')
            ->max();

        return $level;
    }
}
