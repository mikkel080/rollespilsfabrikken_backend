<?php

namespace App\Http\Controllers\Resources;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

// Other
use App\Policies\PolicyHelper;

// Models
use App\Models\Event;
use App\Models\Calendar;

// Requests
use App\Http\Requests\API\Event\Index;
use App\Http\Requests\API\Event\Store;
use App\Http\Requests\API\Event\Update;
use App\Http\Requests\API\Event\Destroy;
use App\Http\Requests\API\Event\Show;

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
        $items = 5;
        if ($request->query('items')) {
            $items = $request->query('items');
        }

        $events = $calendar
            ->events()
            ->latest()
            ->paginate($items);

        return response()->json([
            'message' => 'success',
            'events' => $events,
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
            'post' => $event,
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
        $data['calendar_id'] = $calendar['id'];
        $data['user_id'] = auth()->user()['id'];

        $event = (new Event)->create($data);

        return response()->json( [
            'message' => 'success',
            'event' => $event
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
        $event->update($request->validated());

        return response()->json([
            'message' => 'success',
            'event' => $event
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
