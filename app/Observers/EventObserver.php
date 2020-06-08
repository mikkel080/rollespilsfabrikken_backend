<?php

namespace App\Observers;

use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Support\Arr;

class EventObserver
{
    /**
     * Handle the event "created" event.
     *
     * @param Event $event
     * @return void
     */
    public function created(Event $event)
    {

    }

    /**
     * Handle the event "updated" event.
     *
     * @param Event $event
     * @return void
     */
    public function updated(Event $event)
    {

    }

    /**
     * Handle the event "deleted" event.
     *
     * @param Event $event
     * @return void
     */
    public function deleted(Event $event)
    {

    }

    /**
     * Handle the event "restored" event.
     *
     * @param Event $event
     * @return void
     */
    public function restored(Event $event)
    {
        return;
    }

    /**
     * Handle the event "force deleted" event.
     *
     * @param Event $event
     * @return void
     */
    public function forceDeleted(Event $event)
    {
        return;
    }
}
