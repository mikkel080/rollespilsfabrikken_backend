<?php

namespace App\Policies;

use App\Models\Calendar;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CalendarPolicy
{
    use HandlesAuthorization;

    public function before(User $user, $ability)
    {
        if ($user->isSuperUser()) {
            return true;
        }
    }

    /**
     * Determine whether the user can view any calendars.
     *
     * @param User $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can view the calendar.
     *
     * @param User $user
     * @param Calendar $calendar
     * @return mixed
     */
    public function view(User $user, Calendar $calendar)
    {
        return (new PolicyHelper())->checkLevel($user,  $calendar['obj_id'], 2);
    }

    /**
     * Determine whether the user can create calendars.
     *
     * @param User $user
     * @return mixed
     */
    public function create(User $user)
    {
        return $user->isSuperUser();
    }

    /**
     * Determine whether the user can update the calendar.
     *
     * @param User $user
     * @param Calendar $calendar
     * @return mixed
     */
    public function update(User $user, Calendar $calendar)
    {
        return $user->isSuperUser();
    }

    /**
     * Determine whether the user can delete the calendar.
     *
     * @param User $user
     * @param Calendar $calendar
     * @return mixed
     */
    public function delete(User $user, Calendar $calendar)
    {
        return $user->isSuperUser();
    }

    /**
     * Determine whether the user can restore the calendar.
     *
     * @param User $user
     * @param Calendar $calendar
     * @return mixed
     */
    public function restore(User $user, Calendar $calendar)
    {
        return $user->isSuperUser();
    }

    /**
     * Determine whether the user can permanently delete the calendar.
     *
     * @param User $user
     * @param Calendar $calendar
     * @return mixed
     */
    public function forceDelete(User $user, Calendar $calendar)
    {
        return $user->isSuperUser();
    }
}
