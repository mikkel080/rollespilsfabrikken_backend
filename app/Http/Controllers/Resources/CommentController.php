<?php

namespace App\Http\Controllers\Resources;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

// Models
use App\Models\Post;
use App\Models\Comment;
use App\Models\Forum;

// Requests
use App\Http\Requests\API\Comment\Index;
use App\Http\Requests\API\Comment\Store;
use App\Http\Requests\API\Comment\Update;
use App\Http\Requests\API\Comment\Destroy;
use App\Http\Requests\API\Comment\Show;
use Illuminate\Http\Response;

class CommentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Index $request
     * @param Forum $forum
     * @param Post $post
     * @return JsonResponse
     */
    public function index(Index $request, Forum $forum, Post $post)
    {
        $items = 5;
        if ($request->query('items')) {
            $items = $request->query('items');
        }

        $comments = $post
            ->comments()
            ->latest()
            ->paginate($items);

        return response()->json([
            'message' => 'success',
            'comments' => $comments,
        ], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param Show $request
     * @param Forum $forum
     * @param Post $post
     * @param Comment $comment
     * @return JsonResponse
     */
    public function show(Show $request, Forum $forum, Post $post, Comment $comment)
    {
        return response()->json([
            'message' => 'success',
            'comment' => $comment,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Store $request
     * @param Forum $forum
     * @param Post $post
     * @return JsonResponse
     */
    public function store(Store $request, Forum $forum, Post $post)
    {
        $data = $request->validated();
        $data['post_id'] = $post['id'];

        if (Arr::has($data, 'parent_id')) {
            if (Comment::find($data['parent_id'])['post_id'] != $data['post_id']) {
                return response()->json( [
                    'message' => 'The parent comment is not part of this post.',
                ], 400);
            }
        }
        $data['user_id'] = auth()->user()['id'];

        $comment = (new Comment)->create($data);

        return response()->json( [
            'message' => 'success',
            'comment' => $comment
        ], 201);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param Update $request
     * @param Forum $forum
     * @param Post $post
     * @param Comment $comment
     * @return JsonResponse
     */
    public function update(Update $request, Forum $forum, Post $post, Comment $comment)
    {
        $comment->update($request->validated());

        return response()->json([
            'message' => 'success',
            'comment' => $comment
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Forum $forum
     * @param Post $post
     * @param Comment $comment
     * @return JsonResponse
     */
    public function destroy(Forum $forum, Post $post, Comment $comment)
    {
        $comment->delete();

        return response()->json([
            'message' => "success"
        ], 200);
    }
}
