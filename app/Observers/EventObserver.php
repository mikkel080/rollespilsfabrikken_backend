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
        if(!$event->event()->exists()) {
            $recurrences = [
                'daily'     => [
                    'times'     => 365,
                    'function'  => 'addDay'
                ],
                'weekly'    => [
                    'times'     => 52,
                    'function'  => 'addWeek'
                ],
                'monthly'    => [
                    'times'     => 12,
                    'function'  => 'addMonth'
                ]
            ];

            $startTime  = Carbon::parse($event->start);
            $endTime    = Carbon::parse($event->end);

            $user       = $event->user;
            $calendar   = $event->calendar;

            $recurrence = $recurrences[$event->recurrence] ?? null;

            if($recurrence) {
                for($i = 0; $i < $recurrence['times']; $i++) {
                    $startTime->{$recurrence['function']}();
                    $endTime->{$recurrence['function']}();

                    $newEvent = (new Event)->fill([
                        'title'         => $event->title,
                        'description'   => $event->description,
                        'start'    => $startTime,
                        'end'      => $endTime,
                        'recurrence'    => $event->recurrence,
                    ]);

                    $newEvent->user()->associate($user);
                    $newEvent->calendar()->associate($calendar);

                    $event->events()->save($newEvent);
                }
            }
        }
    }

    /**
     * Handle the event "updated" event.
     *
     * @param Event $event
     * @return void
     */
    public function updated(Event $event)
    {
        if ($event->events()->exists() || $event->event) {
            $startTime = Carbon::parse($event->getOriginal('start_time'))->diffInSeconds($event->start_time, false);
            $endTime = Carbon::parse($event->getOriginal('end_time'))->diffInSeconds($event->end_time, false);

            if($event->event) {
                $childEvents = $event->event->events()->whereDate('start_time', '>', $event->getOriginal('start_time'))->get();
            } else {
                $childEvents = $event->events;
            }

            foreach($childEvents as $childEvent) {
                if($startTime) {
                    $childEvent->start_time = Carbon::parse($childEvent->start_time)->addSeconds($startTime);
                }

                if($endTime) {
                    $childEvent->end_time = Carbon::parse($childEvent->end_time)->addSeconds($endTime);
                }

                if($event->isDirty('title') && $childEvent->title == $event->getOriginal('title')) {
                    $childEvent->title = $event->title;
                }

                $childEvent->saveQuietly();
            }
        }

        if($event->isDirty('recurrence') && $event->recurrence != 'none') {
            self::created($event);
        }
    }

    /**
     * Handle the event "deleted" event.
     *
     * @param Event $event
     * @return void
     */
    public function deleted(Event $event)
    {
        if($event->events()->exists()) {
            $events = $event->events()->pluck('id');
        } else if($event->event) {
            $events = $event->event->events()->whereDate('start_time', '>', $event->start_time)->pluck('id');
        } else {
            $events = [];
        }

        (new Event)->whereIn('id', $events)->delete();
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
