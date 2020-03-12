<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;
use App\Models\Calendar;

use Illuminate\Auth\Access\HandlesAuthorization;

class EventPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any events.
     *
     * @param User $user
     * @param Calendar $calendar
     * @return mixed
     */
    public function viewAny(User $user, Calendar $calendar)
    {
        return (new PolicyHelper())->checkLevel($user,  $calendar['obj_id'], 2);
    }

    /**
     * Determine whether the user can view the event.
     *
     * @param User $user
     * @param Event $event
     * @return mixed
     */
    public function view(User $user, Event $event)
    {
        return (new PolicyHelper())->checkLevel($user,  $event->calendar['obj_id'], 2);
    }

    /**
     * Determine whether the user can create events.
     *
     * @param User $user
     * @return mixed
     */
    public function create(User $user, Calendar $calendar)
    {
        return (new PolicyHelper())->checkLevel($user,  $calendar['obj_id'], 4);
    }

    /**
     * Determine whether the user can update the event.
     *
     * @param User $user
     * @param Event $event
     * @return mixed
     */
    public function update(User $user, Event $event)
    {
        if ($event['user_id'] == $user['id']) return true;

        return (new PolicyHelper())->checkLevel($user,  $event->calendar['obj_id'], 5);
    }

    /**
     * Determine whether the user can delete the event.
     *
     * @param User $user
     * @param Event $event
     * @return mixed
     */
    public function delete(User $user, Event $event)
    {
        if ($event['user_id'] == $user['id']) return true;

        return (new PolicyHelper())->checkLevel($user,  $event->calendar['obj_id'], 5);
    }

    /**
     * Determine whether the user can restore the event.
     *
     * @param User $user
     * @param Event $event
     * @return mixed
     */
    public function restore(User $user, Event $event)
    {
        return (new PolicyHelper())->checkLevel($user,  $event->calendar['obj_id'], 5);
    }

    /**
     * Determine whether the user can permanently delete the event.
     *
     * @param User $user
     * @param Event $event
     * @return mixed
     */
    public function forceDelete(User $user, Event $event)
    {
        return (new PolicyHelper())->checkLevel($user,  $event->calendar['obj_id'], 5);
    }
}
