<?php

namespace App\Http\Controllers\Resources;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Event\Destroy;
use App\Http\Requests\API\Event\Index;
use App\Http\Requests\API\Event\Show;
use App\Http\Requests\API\Event\Store;
use App\Http\Requests\API\Event\Update;
use App\Models\Calendar;
use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Helpers;
use App\Http\Resources\Event\EventWithUserCollection as EventWithUserCollection;
use App\Http\Resources\Event\EventWithUser as EventWithUser;
use App\Http\Resources\Event\EventCollection as EventCollection;
use App\Http\Resources\Event\Event as EventResource;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Index $request
     * @param Calendar $calendar
     * @return JsonResponse
     */
    public function index(Index $request, Calendar $calendar)
    {
        if ($request->query('search')) {
            $events = (new Helpers())->searchItems($request, Event::class, [
                [
                    'key' => 'forum_id',
                    'value' => $calendar['id']
                ]
            ]);
        } else {
            $events = $calendar->events()->getQuery();

            if ($request->query('timeMax') && $date = (new Helpers())->convertDate($request->query('timeMax'))) {
                $events->where('start', '<', $date);
            }

            if ($request->query('timeMin') && $date = (new Helpers())->convertDate($request->query('timeMin'))) {
                $events->where('start', '>', $date);
            }

            $events = (new Helpers())->filterItems($request, $events);
        }

        return response()->json([
            'message' => 'success',
            'data' => new EventWithUserCollection($events),
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
        $data['start'] = Carbon::createFromFormat('d-m-Y H:i:s', $data['start'])->toDateTimeString();
        $data['end'] = Carbon::createFromFormat('d-m-Y H:i:s', $data['end'])->toDateTimeString();
        $event = (new Event())
            ->fill($data)
            ->user()
            ->associate(auth()->user());

        $calendar->events()->save($event);

        return response()->json( [
            'message' => 'success',
            'event' => new EventResource($event)
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
        $data = $request->validated();
        $data['start'] = Carbon::createFromFormat('d-m-Y H:i:s', $data['start'])->toDateTimeString();
        $data['end'] = Carbon::createFromFormat('d-m-Y H:i:s', $data['end'])->toDateTimeString();

        $event->update($data);

        return response()->json([
            'message' => 'success',
            'event' => new EventResource($event)
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
        $event->delete();

        return response()->json([
            'message' => "success"
        ], 200);
    }
}
