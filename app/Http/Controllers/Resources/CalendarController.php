<?php

namespace App\Http\Controllers\Resources;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Helpers;
use App\Http\Requests\API\Calendar\Index;
use App\Http\Requests\API\Calendar\Destroy;
use App\Http\Requests\API\Calendar\Show;
use App\Http\Requests\API\Calendar\Store;
use App\Http\Requests\API\Calendar\Update;
use App\Http\Resources\Calendar\CalendarWithEvents as CalendarWithEvents;
use App\Models\Calendar;
use App\Models\Obj;
use App\Policies\PolicyHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\Calendar\Calendar as CalendarResource;
use App\Http\Resources\Calendar\CalendarCollection as CalendarCollection;

class CalendarController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Index $request
     * @return JsonResponse
     */
    public function index(Index $request)
    {
        $user = auth()->user();

        $calendars = Calendar::query();

        if (!$user->isSuperUser()) {
            $calendars = $calendars
                ->whereIn('obj_id', collect($user->permissions())
                    ->where('level', '>', 1)
                    ->pluck('obj_id')
                );
        }

        $calendars = (new Helpers())->filterItems($request, $calendars);

        return response()->json([
            'message' => 'success',
            'data' => new CalendarCollection($calendars)
        ], 200);
    }


    /**
     * Display the specified resource.
     *
     * @param Show $request
     * @param Calendar $calendar
     * @return JsonResponse
     */
    public function show(Show $request, Calendar $calendar)
    {
        $calendar['access_level'] = (new PolicyHelper)->getLevel(auth()->user(), $calendar['obj_id']);

        return response()->json([
            'message' => 'success',
            'calendar' => new CalendarWithEvents($calendar),
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Store $request
     * @return JsonResponse
     */
    public function store(Store $request)
    {
        $calendar = (new Calendar())
            ->fill($request->validated())
            ->obj()
            ->associate((new Obj)->create([
                'type' => 'calendar'
            ]));
        $calendar->save();

        return response()->json([
            'message' => 'success',
            'calendar' => new CalendarResource($calendar->refresh())
        ], 201);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param Update $request
     * @param Calendar $calendar
     * @return JsonResponse
     */
    public function update(Update $request, Calendar $calendar)
    {
        $calendar->update( $request->validated());

        return response()->json([
            'message' => 'success',
            'calendar' => new CalendarResource($calendar)
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Destroy $request
     * @param Calendar $calendar
     * @return JsonResponse
     */
    public function destroy(Destroy $request, Calendar $calendar)
    {
        $calendar = $calendar->delete();

        return response()->json([
            'message' => 'success'
        ], 200);
    }
}
