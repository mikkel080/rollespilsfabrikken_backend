<?php

namespace App\Http\Controllers\Resources;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Comment\Index;
use App\Http\Requests\API\Comment\Show;
use App\Http\Requests\API\Comment\Store;
use App\Http\Requests\API\Comment\Update;
use App\Models\Comment;
use App\Models\Forum;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use App\Http\Controllers\Helpers;

// Models

// Requests

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
        $comments = $post
            ->comments()
            ->where('parent_id', '=', null)
            ->with('childComments')
            ->getQuery();

        $comments = (new Helpers())->filterItems($request, $comments);

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

        $comment = (new Comment())->fill($data);
        $comment->user()->associate(auth()->user());
        $comment->post()->associate($post);

        $data['post_id'] = $post['id'];

        if (Arr::has($data, 'parent_id')) {
            $parentComment = (new Comment)->findOrFail($data['parent_id']);

            if ($post['id'] != $parentComment['post_id']) {
                return response()->json( [
                    'message' => 'The parent comment is not part of this post.',
                ], 400);
            } else {
                $parentComment->comments()->save($comment);
            }
        } else {
            $comment->save();
        }

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
