<?php

namespace App\Http\Controllers\Resources;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Calendar\Destroy;
use App\Http\Requests\API\Calendar\Show;
use App\Http\Requests\API\Calendar\Store;
use App\Http\Requests\API\Calendar\Update;
use App\Models\Calendar;
use App\Models\Obj;
use App\Policies\PolicyHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

// Models

// Requests
//use App\Http\Requests\API\Calendar\Index;

class CalendarController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index()
    {
        $user = auth()->user();

        if ($user->isSuperUser()) {
            $forums = Calendar::all();
        } else {
            $perms = collect($user->permissions())->pluck("obj_id");
            $forums = DB::table('calendar')
                ->whereIn('obj_id', $perms)
                ->get();
        }

        return response()->json([
            'message' => 'success',
            'forums' => $forums
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

        $calendar['posts'] = $calendar
            ->events()
            ->latest()
            ->paginate(10);

        return response()->json([
            'message' => 'success',
            'calendar' => $calendar,
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
        $obj = (new Obj)->create([
            'type' => 'calendar'
        ]);

        $data = $request->validated();
        $data['obj_id'] = $obj['id'];
        $calendar = (new Calendar)->create($data);

        return response()->json([
            'message' => 'success',
            'calendar' => $calendar
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
        $data = $request->validated();
        $calendar->update($data);

        return response()->json([
            'message' => 'success',
            'calendar' => $calendar
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
