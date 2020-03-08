<?php

namespace App\Http\Controllers\Resources;

use App\Models\User;
use App\Models\Forum;
use App\Models\Obj;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

// Requests
use App\Http\Requests\API\Forum\Index;
use App\Http\Requests\API\Forum\Store;
use App\Http\Requests\API\Forum\Update;
use App\Http\Requests\API\Forum\Destroy;
use App\Http\Requests\API\Forum\Show;

class ForumController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index()
    {
        $user = auth()->user();

        $perms = collect($user->permissions())->pluck("obj_id");
        $forums = DB::table('forums')->get();

        $forums = $forums->whereIn('obj_id', $perms);

        return response()->json([
            'message' => 'success',
            'forums' => $forums
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
        $user = auth()->user();

        $obj = (new \App\Models\Obj)->create([
            'type' => 'forum'
        ]);

        $data = $request->validated();
        $data['obj_id'] = $obj['id'];
        $forum = (new Forum)->create($data);

        return response()->json([
            'message' => 'succes',
            'forum' => $forum
        ], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Forum  $forum
     * @return \Illuminate\Http\Response
     */
    public function show(Forum $forum)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param  \App\Models\Forum  $forum
     * @return \Illuminate\Http\Response
     */
    public function update(Update $request, Forum $forum)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Forum  $forum
     * @return \Illuminate\Http\Response
     */
    public function destroy(Forum $forum)
    {
        //
    }
}
