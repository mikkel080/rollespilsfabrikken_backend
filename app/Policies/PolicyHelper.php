<?php


namespace App\Policies;

use App\Models\User;

class PolicyHelper
{
    public function getLevel(User $user, $objectId, $minimum) {
        $level = collect($user->permissions())
            ->where('obj_id', '=', $objectId)
            ->pluck('level')
            ->max();

        if ($level >= $minimum) {
            return true;
        }

        return false;
    }
}
