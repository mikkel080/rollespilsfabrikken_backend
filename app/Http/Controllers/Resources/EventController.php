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
        $event->start = self::convertStandardToCarbon(Carbon::createFromTimestamp($timestamp)->format('d-m-Y') . ' ' . $event->start)->format('Y-m-d\TH:i:s.v\Z');
        $event->end = self::convertStandardToCarbon(Carbon::createFromTimestamp($timestamp)->format('d-m-Y') . ' ' . $event->end)->format('Y-m-d\TH:i:s.v\Z');
        $event->type = $this->getRepeatIntervalAsString($event['repeat_interval']);

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
                ->whereRaw('(? - cast(repeat_start as signed)) % repeat_interval = 0', $timestamp)
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
        $startTime = Carbon::createFromTimeString($event->start);
        $endTime = Carbon::createFromTimeString($event->end);

        $event->start = Carbon::parse($request['date'])
            ->minutes($startTime->minute)
            ->hours($startTime->hour)
            ->seconds($startTime->second);

        $event->end = Carbon::parse($request['date'])
            ->minutes($endTime->minute)
            ->hours($endTime->hour)
            ->seconds($endTime->second);

        $event = collect($event)->merge($event->meta)->toArray();
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
        $data = $request->validated();
        $start = Carbon::parse($data['start'])->utc();
        $end = Carbon::parse($data['end'])->utc();

        $data['start'] = $start->toTimeString();
        $data['end'] = $end->toTimeString();

        if ($start->isAfter($end)) {
            return response()->json( [
                'message' => 'An events start date cant be after its end',
            ], 401);
        }

        $event = (new Event())
            ->fill($data)
            ->user()
            ->associate(auth()->user());

        $series = (new EventSerie())->create();
        $event->series()->associate($series);

        $eventData = [
            'repeat_start' => $start->copy()->startOfDay()->timestamp,
            'repeat_interval' => 0,
            'repeat_end' => null
        ];

        if ($data['recurring']) {
            $eventData['repeat_interval'] = self::getRepeatInterval($data['recurrence']['type']);
        }

        if (isset($data['recurrence']['end'])) {
            $eventData['repeat_end'] = Carbon::parse($data['recurrence']['end'])->addDay()->startOfDay()->timestamp;
        }

        $eventMeta = (new EventMeta())->fill($eventData);

        $calendar->events()->save($event);

        $event->refresh()->meta()->save($eventMeta);

        // Set the recurrence type
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

        // Get start and end without date
        $data['start'] = $start->toTimeString();
        $data['end'] = $end->toTimeString();

        // Get the original data
        $originalEventMeta = $event->meta;
        $originalEvent = $event;

        // Standalone events:
        if ($originalEventMeta['repeat_interval'] == 0 && $data['recurring'] == false) {
            $event->update($data);

            $event['type'] = self::getRepeatIntervalAsString($originalEventMeta['repeat_interval']);
            $event['start'] = $start->toISOString();
            $event['end'] = $end->toISOString();

            return response()->json([
                'message' => 'success',
                'event' => new EventResource(collect($event)->merge($originalEventMeta)->toArray())
            ], 200);
        }
        if (isset($data['recurrence']['apply_to_all']) && ($data['recurrence']['apply_to_all'] === true)) {
            // Update the original event
            $event->update($data);
        } else {
            // Create a new event, aka the split
            $event = (new Event())
                ->fill($data);

            $event->user()
                ->associate(auth()->user());

            // Assign the new event to the series of the first event
            $event->series()
                ->associate($originalEvent->series);

            // Save it
            $calendar->events()->save($event);
        }

        // Parse the recurrence data, only with dates this time.
        $eventData = [
            'repeat_start' => $start->startOfDay()->timestamp,
            'repeat_interval' => 0,
            'repeat_end' => null
        ];

        // Set the repeat interval
        if ($data['recurring']) {
            $eventData['repeat_interval'] = self::getRepeatInterval($data['recurrence']['type']);
        }

        // Set the optional end of recurrence
        if (isset($data['recurrence']['repeat_end'])) {
            $eventData['repeat_end'] = Carbon::parse($data['recurrence']['repeat_end'])->addDay()->startOfDay()->timestamp;
        }

        if (isset($data['recurrence']['series']) && ($data['recurrence']['series'] === true)) {
            $series = $event->series;

            foreach ($series->events as $event) {
                $tmpMeta = $event->meta;

                $event->title = $data['title'];
                $event->description = $data['description'];

                $tmpMeta->repeat_interval = self::getRepeatInterval($data['recurrence']['type']);
            }

        }else if (isset($data['recurrence']['apply_to_all']) && ($data['recurrence']['apply_to_all'] === true)) {
            // Update the recurrence of the current branch of the series
            $originalEventMeta->update($eventData);

        } else {
            // This part will split the recurrence series into 2.
            // It will end the original and create a new one.

            // Set an end date to the original recurrence series
            $originalEventMeta['repeat_end'] = $start->startOfDay()->timestamp;
            $originalEventMeta->save();

            // Create a new recurrence series and save it to the new event
            $eventMeta = (new EventMeta())->fill($eventData);
            $event->refresh()->meta()->save($eventMeta);
        }

        // Set the recurrence type
        $event['type'] = self::getRepeatIntervalAsString($originalEventMeta['repeat_interval']);
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
        $data = $request->validated();

        if ($event->meta['repeat_interval'] == 0) {
            $event->delete();
            $event->meta->delete();
            $event->series->delete();

        } else if (isset($data['series']) && ($data['series'] === true)) {
            $series = $event->series;
            $events = $series->events;

            $events->each(function(Event $event, $item) {
                $event->meta->delete();
                $event->delete();
            });

            $series->delete();
        } else {
            $date = Carbon::parse($data['date']);

            if  (isset($data['apply_to_all']) && ($data['apply_to_all'] === true)) {
                $meta = $event->meta;

                $meta['repeat_end'] = $date->startOfDay()->timestamp;
                $meta->save();

            } else {
                $originalMeta = $event->meta;
                $originalEvent = $event;

                $eventData = [
                    'repeat_start' => $date->startOfDay()->timestamp + $originalMeta['repeat_interval'],
                    'repeat_interval' => $originalMeta['repeat_interval'],
                    'repeat_end' => $originalMeta['repeat_end']
                ];

                $originalMeta['repeat_end'] = $date->startOfDay()->timestamp;
                $originalMeta->save();

                $meta = (new EventMeta())->fill($eventData);

                $event = (new Event)->fill($event->only(['title', 'description', 'start', 'end']));
                $event->user()->associate($originalEvent->user);
                $event->series()->associate($originalEvent->series);

                $calendar->events()->save($event);
                $event->refresh()->meta()->save($meta);
            }
        }



        return response()->json([
            'message' => 'success'
        ], 200);
    }
}
