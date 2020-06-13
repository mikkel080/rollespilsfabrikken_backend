<?php

namespace App\Http\Controllers\Resources;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Event\Destroy;
use App\Http\Requests\API\Event\Index;
use App\Http\Requests\API\Event\Show;
use App\Http\Requests\API\Event\Store;
use App\Http\Requests\API\Event\All;
use App\Http\Requests\API\Event\Update;
use App\Models\Calendar;
use App\Models\Event;
use App\Models\EventMeta;
use App\Models\EventSerie;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Helpers;
use App\Http\Resources\Event\EventWithUserCollection as EventWithUserCollection;
use App\Http\Resources\Event\EventWithUser as EventWithUser;
use App\Http\Resources\Event\EventCollection as EventCollection;
use App\Http\Resources\Event\Event as EventResource;
use Illuminate\Support\Facades\DB;
use function PHPUnit\Framework\isNull;

class EventController extends Controller
{
    private function getRepeatInterval($repeat) {
        switch ($repeat) {
            case 'daily':
                return 86400;
            case 'weekly':
                return (86400 * 7);
            case 'monthly':
                return (86400 * 7) * 4;
            case 'yearly':
                return 86400 * 365;
            default:
                return 0;
        }
    }

    private function getRepeatIntervalAsString($interval) {
        switch ($interval) {
            case 86400:
                return 'daily';
            case (86400 * 7):
                return 'weekly';
            case (86400 * 7) * 4:
                return 'monthly';
            case 86400 * 365:
                return 'yearly';
            default:
                return 'none';
        }
    }

    private function convertStandardToCarbon($date) {
        return Carbon::createFromFormat('d-m-Y H:i:s', $date);
    }

    private function convertEvent($event, $timestamp) {
        $start = self::convertStandardToCarbon(Carbon::createFromTimestamp($timestamp)->format('d-m-Y') . ' ' . $event->start);
        $event->start = $start->format('Y-m-d\TH:i:s.v\Z');
        $event->end = $start->addSeconds($event->event_length)->format('Y-m-d\TH:i:s.v\Z');
        $event->type = $this->getRepeatIntervalAsString($event['repeat_interval']);

        return $event;
    }

    private function checkForEventInstance(Event $event, $timestamp) {
        $meta = $event->meta;

        // Check if date is before the repetition start
        if ($meta['repeat_start'] > $timestamp) {
            return false;
        }

        // Check if date is after repition end
        if ($meta['repeat_end'] != null) {
            $end = Carbon::parse($meta['repeat_end'])->addDays(-1);

            if ($end->timestamp < $timestamp) {
                return false;
            }
        }

        // Perform and validate the repeating calculation
        if ($meta['repeat_interval'] != 0 && (($timestamp - $meta['repeat_start']) % $meta['repeat_interval']) != 0 ) {
            return false;
        }

        // Get the start of the event
        $startTime = Carbon::createFromTimeString($event['start']);

        // Combine it with the date
        $event['start'] = Carbon::createFromTimestamp($timestamp)
            ->minutes($startTime->minute)
            ->hours($startTime->hour)
            ->seconds($startTime->second);

        // Calculate the end time of the event
        $event['end'] = $event['start']->copy()->addSeconds($event['event_length']);

        if ($event['end']->timestamp < $timestamp) {
            return response()->json([
                'message' => 'There is no instance of this event on that date'
            ], 404);
        }

        return $event;
    }
    /**
     * Display a listing of the resource.
     * Url : /api/forum/{forum}/posts
     *
     * @param Newest $request
     * @param Forum $forum
     * @return JsonResponse
     */
    public function all(All $request, Calendar $calendar)
    {
        $user = auth()->user();

        $calendars = Calendar::query();

        if (!$user->isSuperUser()) {
            $calendars = $calendars
                ->whereIn('obj_id',
                    collect($user->permissions())
                        ->where('level', '>', 1)
                        ->pluck('obj_id')
                );
        }

        $calendars
            ->select('id')
            ->get();

        $query = Event::query()
            ->whereIn('calendar_id', $calendars);

        if (!$request->query('start') && !$request->query('end')) {
            $startDate = Carbon::now();
            $endDate = Carbon::now()->addDays(7);
        } else {
            $startDate = Carbon::parse($request->query('start'));
            $endDate = Carbon::parse($request->query('end'));
        }


        if (!$startDate || !$endDate) {
            return response()->json([
                'message' => 'That start or/and end does not fit the approved formats' // TODO: Update
            ]);
        }

        $totalEvents = [];
        for($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $timestamp = $date->startOfDay()->timestamp;
            $events = Event::query()
                ->rightJoin('event_metas', 'event_metas.event_id', '=', 'events.id')
                ->where('repeat_start', '<=', $timestamp)
                ->where(function($query) use ($timestamp) {
                    return $query->whereNull('repeat_end')
                        ->orWhere('repeat_end', '>', $timestamp);
                })
                ->whereRaw('(? - cast(repeat_start as signed)) % repeat_interval = 0', $timestamp)
                ->whereIn('calendar_id', $calendars)
                ->get();

            $oneTime = Event::query()
                ->rightJoin('event_metas', 'event_metas.event_id', '=', 'events.id')
                ->where('repeat_start', '=', $timestamp)
                ->where('repeat_interval', '=', 0)
                ->whereIn('calendar_id', $calendars)
                ->get();

            $events->each(function($event, $item) use ($timestamp) {
                self::convertEvent($event, $timestamp);
            });

            $oneTime->each(function($event, $item) use ($timestamp) {
                self::convertEvent($event, $timestamp);
            });

            $totalEvents[] = $events->merge($oneTime);
        }

        $events = collect($totalEvents)->flatten();
        //$events = (new Helpers())->filterItems($request, $events);

        return response()->json([
            'message' => 'success',
            'data' => EventWithUser::collection($events),
        ], 200);
    }

    /**
     * Display a listing of the resource.
     *
     * @param Index $request
     * @param Calendar $calendar
     * @return JsonResponse
     */
    public function index(Index $request, Calendar $calendar)
    {
        if (!$request->query('start') && !$request->query('end')) {
            $startDate = Carbon::now();
            $endDate = Carbon::now()->addDays(7);
        } else {
            $startDate = Carbon::parse($request->query('start'))->startOfDay();
            $endDate = Carbon::parse($request->query('end'))->startOfDay();
        }

        if (!$startDate || !$endDate) {
            return response()->json([
                'message' => 'That start or/and end does not fit the approved formats' // TODO: Update
            ]);
        }

        $totalEvents = [];
        for($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $timestamp = $date->startOfDay()->timestamp;
            $events = $calendar
                ->events()
                ->rightJoin('event_metas', 'event_metas.event_id', '=', 'events.id')
                ->where('repeat_start', '<=', $timestamp)
                ->where(function($query) use ($timestamp) {
                    return $query->whereNull('repeat_end')
                        ->orWhere('repeat_end', '>', $timestamp);
                })
                ->whereRaw('(? - cast(repeat_start as signed)) % repeat_interval = 0', $timestamp)
                ->get();

            $oneTime = $calendar
                ->events()
                ->rightJoin('event_metas', 'event_metas.event_id', '=', 'events.id')
                ->where('repeat_start', '=', $timestamp)
                ->where('repeat_interval', '=', 0)
                ->get();

            $events->each(function($event, $item) use ($timestamp) {
                self::convertEvent($event, $timestamp);
            });

            $oneTime->each(function($event, $item) use ($timestamp) {
                self::convertEvent($event, $timestamp);
            });

            $totalEvents[] = $events->merge($oneTime);
        }

        $events = collect($totalEvents)->flatten();
        //$events = (new Helpers())->filterItems($request, $events);

        return response()->json([
            'message' => 'success',
            'data' => EventWithUser::collection($events),
        ], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param Show $request
     * @param Calendar $calendar
     * @param Event $event
     * @return JsonResponse
     */
    public function show(Show $request, Calendar $calendar, Event $event)
    {
        // Parse the date from the request
        $date = Carbon::parse($request['date']);

        $event = self::checkForEventInstance($event, $date->copy()->startOfDay()->timestamp);

        if ($event == false) {
            return response()->json([
                'message' => 'There is no instance of this event on that date'
            ], 404);
        }

        // Combine the event data
        $event = collect($event)->merge($event->meta)->toArray();

        // Configure the data so its ready for viewing
        $event['type'] = self::getRepeatIntervalAsString($event['repeat_interval']);
        $event['start'] = $event['start']->format('Y-m-d\TH:i:s.v\Z');
        $event['end'] = $event['end']->format('Y-m-d\TH:i:s.v\Z');

        return response()->json([
            'message' => 'success',
            'post' => new EventWithUser($event),
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Store $request
     * @param Calendar $calendar
     * @return JsonResponse
     */
    public function store(Store $request, Calendar $calendar)
    {
        // Parse the request data
        $data = $request->validated();
        $start = Carbon::parse($data['start'])->utc();
        $end = Carbon::parse($data['end'])->utc();

        // Set the saved data
        $data['start'] = $start->toTimeString();
        $data['event_length'] = $start->diffInSeconds($end); // Get difference between start and end to allow for multidate

        // Make sure the start is not after end date
        if ($start->isAfter($end)) {
            return response()->json( [
                'message' => 'An events start date cant be after its end',
            ], 401);
        }

        // Create the new event
        $event = (new Event())
            ->fill($data);

        // Associate with the creating user
        $event
            ->user()
            ->associate(auth()->user());

        // Create a new series and associate with the event
        $series = (new EventSerie())->create();
        $event->series()->associate($series);

        // Define initial meta data
        $metaData = [
            'repeat_start' => $start->copy()->startOfDay()->timestamp,
            'repeat_interval' => 0,
            'repeat_end' => null
        ];

        // Set recurring if the event is recurring
        if ($data['recurring']) {
            $metaData['repeat_interval'] = self::getRepeatInterval($data['recurrence']['type']);
        }

        // Set an optional recurring end date
        if (isset($data['recurrence']['end'])) {
            $metaData['repeat_end'] = Carbon::parse($data['recurrence']['end'])->addDay()->startOfDay()->timestamp;
        }

        // Save the event
        $calendar->events()->save($event);

        // Fill the event meta data
        $eventMeta = (new EventMeta())->fill($metaData);

        // Save and associate the metadata with the even
        $event->refresh()->meta()->save($eventMeta);

        // Set the event data such that it is ready for showing
        $event['type'] = self::getRepeatIntervalAsString($eventMeta['repeat_interval']);
        $event['start'] = $start->format('Y-m-d\TH:i:s.v\Z');
        $event['end'] = $end->format('Y-m-d\TH:i:s.v\Z');

        return response()->json( [
            'message' => 'success',
            'event' => new EventResource(collect($event)->merge($eventMeta->refresh())->toArray())
        ], 201);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Update $request
     * @param Calendar $calendar
     * @param Event $event
     * @return JsonResponse
     */
    public function update(Update $request, Calendar $calendar, Event $event)
    {
        // Retrieve the data
        $data = $request->validated();

        // Parse start date
        $start = Carbon::parse($data['start']);
        $end = Carbon::parse($data['end']);

        // Make sure the start is not after end date
        if ($start->isAfter($end)) {
            return response()->json( [
                'message' => 'An events start date cant be after its end',
            ], 401);
        }

        // Set the start time, without date, and define the event length in seconds
        $data['start'] = $start->toTimeString();
        $data['event_length'] = $start->diffInSeconds($end);

        // Create a copy of the original data
        $originalEventMeta = $event->meta;
        $originalEvent = $event;

        // Parse the recurrence data, only with dates this time.
        $metaData = [
            'repeat_start' => $start->startOfDay()->timestamp,
            'repeat_interval' => 0,
            'repeat_end' => null
        ];

        // Set the repeat interval
        if ($data['recurring']) {
            $metaData['repeat_interval'] = self::getRepeatInterval($data['recurrence']['type']);
        }

        // Set the optional end of recurrence
        if (isset($data['recurrence']['end'])) {
            $metaData['repeat_end'] = Carbon::parse($data['recurrence']['end'])->addDay()->startOfDay()->timestamp;
        }

        // Standalone events dont have a series or anything special to them
        // Therefore if updated they can just be completely updated
        if ($originalEventMeta['repeat_interval'] == 0) {
            $event->update($data);
            $originalEventMeta->update($metaData);

            $event['type'] = self::getRepeatIntervalAsString($originalEventMeta['repeat_interval']);
            $event['start'] = $start->toISOString();
            $event['end'] = $end->toISOString();

            return response()->json([
                'message' => 'success',
                'event' => new EventResource(collect($event)->merge($originalEventMeta)->toArray())
            ], 200);

        // Update the series
        } else if (isset($data['recurrence']['series']) && ($data['recurrence']['series'] === true)) {
            // Get all the events in the series
            $series = $event->series;

            // Loop them
            foreach ($series->events as $event) {
                // Update the title
                $event->title = $data['title'];

                // Update the description
                $event->description = $data['description'];

                // Update start
                $event->start = $data['start'];
                $event->event_length = $data['event_length'];

                // Get the metadata for the current event
                $meta = $event->meta;

                // Set the repeat interval
                $meta->repeat_interval = self::getRepeatInterval($data['recurrence']['type']);

                // Save both
                $event->save();
                $meta->save();
            }
        }else if (isset($data['recurrence']['apply_to_all']) && ($data['recurrence']['apply_to_all'] === true)) {
            // Update the original event
            $event->update($data);

            // Update the recurrence of the current branch of the series
            $originalEventMeta->update($metaData);
        } else if (isset($data['recurrence']['only_this']) && ($data['recurrence']['only_this'] === true)){
            $eventData = [
                'repeat_start' => $start->copy()->startOfDay()->timestamp + $originalEventMeta['repeat_interval'],
                'repeat_interval' => $originalEventMeta['repeat_interval'],
                'repeat_end' => $originalEventMeta['repeat_end']
            ];

            $originalEventMeta['repeat_end'] = $start->startOfDay()->timestamp;
            $originalEventMeta->save();

            $meta = (new EventMeta())->fill($eventData);

            $event = (new Event)->fill($event->only(['title', 'description', 'start', 'event_length']));
            $event->user()->associate($originalEvent->user);
            $event->series()->associate($originalEvent->series);

            $calendar->events()->save($event);
            $event->refresh()->meta()->save($meta);
        } else {
            // This will split the recurrence series into 2.
            // It will end the original and create a new one.
            // Create a new event, aka the split
            $event = (new Event())
                ->fill($data);

            // Assign it to the user
            $event
                ->user()
                ->associate(auth()->user());

            // Assign the new event to the series of the first event
            $event
                ->series()
                ->associate($originalEvent->series);

            // Save it
            $calendar->events()->save($event);

            // Set an end date to the original recurrence series
            $originalEventMeta['repeat_end'] = $start->startOfDay()->timestamp;
            $originalEventMeta->save();

            // Create a new recurrence series and save it to the new event
            $eventMeta = (new EventMeta())->fill($metaData);
            $event->refresh()->meta()->save($eventMeta);
        }

        // Set the event data such that it is ready for showing
        $event['type'] = self::getRepeatIntervalAsString($metaData['repeat_interval']);
        $event['start'] = $start->format('Y-m-d\TH:i:s.v\Z');
        $event['end'] = $end->format('Y-m-d\TH:i:s.v\Z');

        return response()->json([
            'message' => 'success',
            'event' => new EventResource(collect($event)->merge($event->meta)->toArray())
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Destroy $request
     * @param Calendar $calendar
     * @param Event $event
     * @return JsonResponse
     */
    public function destroy(Destroy $request, Calendar $calendar, Event $event)
    {
        // Retrieve the data
        $data = $request->validated();

	// Create a copy of the original data
	$meta = $event->meta;

	if ($meta['repeat_interval'] != 0) {
	    $date = Carbon::parse($data['date']);
	}
        
	// Standalone events dont have a series or anything special to them
        // Therefore they can just be deleted
        if ($meta['repeat_interval'] == 0) {
            $event->delete();
            $event->meta->delete();

            // Update the series
        } else if (isset($data['series']) && ($data['series'] === true)) {
            $series = $event->series;
            $events = $series->events;

            $events->each(function(Event $event, $item) {
                $event->meta->delete();
                $event->delete();
            });

            $series->delete();
        } else if (isset($data['apply_to_all']) && ($data['apply_to_all'] === true)) {
            $event->delete();
            $event->meta->delete();
        } else if (isset($data['only_this']) && ($data['only_this'] === true)){
            // Check if there is an event instance on the given date
            $event = self::checkForEventInstance($event, $date->copy()->startOfDay()->timestamp);

            if ($event == false) {
                return response()->json([
                    'message' => 'There is no instance of this event on that date'
                ], 404);
            }

            // Define the new event meta data
            $metaData = [
                'repeat_start' => $date->startOfDay()->timestamp + $meta['repeat_interval'],
                'repeat_interval' => $meta['repeat_interval'],
                'repeat_end' => $meta['repeat_end']
            ];

            $meta['repeat_end'] = $date->startOfDay()->timestamp;
            $meta->save();
            
            // Create a new meta
            $meta = (new EventMeta())->fill($metaData);

            // Create and save a new event that starts right after the one just deleted
            $newEvent = (new Event)->fill($event->only(['title', 'description', 'start', 'event_length']));
            $newEvent->user()->associate($event->user);
            $newEvent->series()->associate($event->series);

            $calendar->events()->save($newEvent);
            $newEvent->refresh()->meta()->save($meta);
        } else {
            // Check if there is an event instance on the given date
            $event = self::checkForEventInstance($event, $date->copy()->startOfDay()->timestamp);

            if ($event == false) {
                return response()->json([
                    'message' => 'There is no instance of this event on that date'
                ], 404);
            }


            $meta['repeat_end'] = $date->startOfDay()->timestamp;
            $meta->save();
        }

        return response()->json([
            'message' => 'success'
        ], 200);
    }
}
