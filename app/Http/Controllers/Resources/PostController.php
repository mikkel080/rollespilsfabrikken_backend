<?php

namespace App\Http\Controllers\Resources;

// Models
use App\Http\Controllers\Controller;
use App\Models\Forum;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

// Helpers

// Requests
use App\Http\Requests\API\Post\Index;
use App\Http\Requests\API\Post\Store;
use App\Http\Requests\API\Post\Update;
use App\Http\Requests\API\Post\Destroy;
use App\Http\Requests\API\Post\Show;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     * Url : /api/forum/{forum}/posts
     *
     * @param Index $request
     * @param Forum $forum
     * @return JsonResponse
     */
    public function index(Index $request,  Forum $forum)
    {
        $items = 5;
        if ($request->query('items')) {
            $items = $request->query('items');
        }

        $posts = $forum
            ->posts()
            ->latest()
            ->paginate($items);

        return response()->json([
            'message' => 'success',
            'posts' => $posts,
        ], 200);
    }

    /**
     * Display the specified resource.
     * Url : /api/forum/{forum}/post/{post}
     *
     * @param Show $request
     * @param Forum $forum
     * @param Post $post
     * @return JsonResponse
     */
    public function show(Show $request, Forum $forum, Post $post)
    {
        return response()->json([
            'message' => 'success',
            'post' => $post,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     * Url : /api/forum/{forum}/post/
     *
     * @param Store $request
     * @param Forum $forum
     * @return JsonResponse
     */
    public function store(Store $request, Forum $forum)
    {
        $data = $request->validated();
        $data['forum_id'] = $forum['id'];
        $data['user_id'] = auth()->user()['id'];

        $post = (new Post)->create($data);

        return response()->json( [
            'message' => 'success',
            'post' => $post
        ], 201);
    }

    /**
     * Update the specified resource in storage.
     * Url : /api/forum/{forum}/post/{post}
     *
     * @param Update $request
     * @param Post $post
     * @return JsonResponse
     */
    public function update(Update $request, Forum $forum, Post $post)
    {
        $post->update($request->validated());

        return response()->json([
            'message' => 'success',
            'post' => $post
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     * Url : /api/forum/{forum}/post/{post}
     *
     * @param Destroy $request
     * @param Post $post
     * @return JsonResponse
     */
    public function destroy(Destroy $request, Forum $forum, Post $post)
    {
        $post->delete();

        return response()->json([
           'data' => "Success"
        ], 200);
    }
}
