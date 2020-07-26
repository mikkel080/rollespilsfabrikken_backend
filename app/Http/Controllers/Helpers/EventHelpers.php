<?php


namespace App\Http\Controllers\Helpers;


use App\Http\Controllers\Helpers\Constants\EventConstants;
use App\Models\Event;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Calendar;

class EventHelpers
{
    public static function convertStandardToCarbon($date) {
        return Carbon::createFromFormat('d-m-Y H:i:s', $date);
    }

    public static function convertEvent($event, $timestamp) {
        $start = self::convertStandardToCarbon(Carbon::createFromTimestamp($timestamp)->format('d-m-Y') . ' ' . $event['start']);
        $end = $start->copy()->addSeconds($event['event_length']);

        $event['start_timestamp'] = $start->timestamp;
        $event['end_timestamp'] = $end->timestamp;

        $event['start'] = $start->format('Y-m-d\TH:i:s.v\Z');
        $event['end']   = $end->format('Y-m-d\TH:i:s.v\Z');

        $event['type'] = EventConstants::$recurrenceStringLookup[$event['repeat_interval']];

        return $event;
    }

    public static function checkForEventInstance(Event $event, $timestamp) {
        $event = self::convertEvent(collect($event)->merge($event->meta)->toArray(), $timestamp);

        // Check if date is before the repetition start or if the date is after the event end
        if ($event['repeat_start'] > $timestamp || $event['end_timestamp'] < $timestamp) {
            return false;
        }

        // Check if date is after repetition end
        if ($event['repeat_end'] != null) {
            $end = Carbon::parse($event['repeat_end'])->addDays(-1);

            if ($end->timestamp < $timestamp) {
                return false;
            }
        }

        // Perform and validate the repeating calculation
        if ($event['repeat_interval'] != 0 && (($timestamp - $event['repeat_start']) % $event['repeat_interval']) != 0 ) {
            return false;
        }

        return $event;
    }

    public static function getEventQuery(Builder $query, bool $recurring, int $timestamp) : Builder {
        $query->rightJoin('event_metas', 'event_metas.event_id', '=', 'events.id');

        if ($recurring) {
            $query
                ->where('repeat_start', '<=', $timestamp)
                ->where(function($query) use ($timestamp) {
                    return $query->whereNull('repeat_end')
                        ->orWhere('repeat_end', '>', $timestamp);
                })
                ->whereRaw('(? - cast(repeat_start as signed)) % repeat_interval = 0', $timestamp);
        } else {
            $query
                ->where('repeat_start', '=', $timestamp)
                ->where('repeat_interval', '=', 0);
        }

        return $query;
    }


    public static function parseRequest($request) {
        if (!$request->query('start') && !$request->query('end')) {
            $startDate = Carbon::now();
            $endDate = Carbon::now()->addDays(7);
        } else {
            $startDate = Carbon::parse($request->query('start'));
            $endDate = Carbon::parse($request->query('end'));
        }

        if (!$startDate || !$endDate) {
            return response()->json([
                'message' => 'That start or/and end does not fit the approved format'
            ]);
        }

        return array($startDate, $endDate);
    }

    public static function parseData($data) {
        // Parse start date
        $start = Carbon::parse($data['start']);
        $end = Carbon::parse($data['end']);

        $data['start_timestamp'] = $start->timestamp;
        $data['end_timestamp'] = $end->timestamp;

        // Set the start time, without date, and define the event length in seconds
        $data['start'] = $start->toTimeString();
        $data['event_length'] = $start->diffInSeconds($end);


        // Parse the recurrence data
        $metaData = [
            'repeat_start' => $start->startOfDay()->timestamp,
            'repeat_interval' => 0,
            'repeat_end' => null
        ];

        // Set the repeat interval
        if ($data['recurring']) {
            $metaData['repeat_interval'] = EventConstants::$recurrenceIntervalLookup[$data['recurrence']['type']];
        }

        // Set the optional end of recurrence
        if (isset($data['recurrence']['end'])) {
            $metaData['repeat_end'] = Carbon::parse($data['recurrence']['end'])->addDay()->startOfDay()->timestamp;
        }

        return array($start, $end, $data, $metaData);
    }

    public static function getCalendars(User $user) {
        $calendars = Calendar::query();

        // Get calendars the user has access to
        if (!$user->isSuperUser()) {
            $calendars = $calendars
                ->whereIn('obj_id',
                    collect($user->permissions())
                        ->where('level', '>', 1)
                        ->pluck('obj_id')
                );
        }

        return $calendars
            ->select('id')
            ->get();
    }

    public static function getEventsInRange($start, $end, $calendars) {
        $events = [];
        for($date = $start->copy(); $date->lt($end); $date->addDay()) {
            $timestamp = $date->startOfDay()->timestamp;

            $recurring = self::getEventQuery(Event::query(), true, $timestamp)
                ->whereIn('calendar_id', $calendars)
                ->get()
                ->each(function($event, $item) use ($timestamp) {
                    self::convertEvent($event, $timestamp);
                });

            $oneTime = self::getEventQuery(Event::query(), false, $timestamp)
                ->whereIn('calendar_id', $calendars)
                ->get()
                ->each(function($event, $item) use ($timestamp) {
                    self::convertEvent($event, $timestamp);
                });

            $events[] = $recurring->merge($oneTime);
        }

        return collect($events)->flatten();
    }

}
