<?php

namespace App\Http\Controllers\Resources;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Helpers\Helpers;
use App\Http\Requests\API\Post\Destroy;
use App\Http\Requests\API\Post\Index;
use App\Http\Requests\API\Post\Newest;
use App\Http\Requests\API\Post\Pin;
use App\Http\Requests\API\Post\Lock;
use App\Http\Requests\API\Post\Show;
use App\Http\Requests\API\Post\Store;
use App\Http\Requests\API\Post\File as AddFile;
use App\Http\Requests\API\Post\DownloadFile;
use App\Http\Requests\API\Post\Update;
use App\Http\Resources\Post\PostIndexCollection;
use App\Http\Resources\Post\PostIndexNewest;
use App\Http\Resources\Post\PostIndexNewestCollection;
use App\Models\Forum;
use App\Models\Post;
use App\Models\PostFile;
use finfo;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\JsonResponse;

use App\Http\Resources\Post\Post as PostResource;
use App\Http\Resources\PostFile\PostFile as PostFileResource;
use App\Http\Resources\Post\PostCollection as PostCollection;
use App\Http\Resources\Post\PostWithUser as PostWithUserResource;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Helpers\FileHelpers;

class PostAttributeController extends Controller
{
    /**
     * Pin the post
     * Url : /api/forum/{forum}/post/{post}/pin
     *
     * @param Pin $request
     * @param Forum $forum
     * @param Post $post
     * @return JsonResponse
     */
    public function pin(Pin $request, Forum $forum, Post $post)
    {
        if ($post->pinned) {
            $post->pinned = false;
        } else {
            $post->pinned = true;
        }

        $post->save();

        return response()->json([
            'message' => 'success',
            'post' => new PostResource($post)
        ], 200);
    }

    /**
     * Lock the post
     * Url : /api/forum/{forum}/post/{post}/lock
     *
     * @param Lock $request
     * @param Forum $forum
     * @param Post $post
     * @return JsonResponse
     */
    public function lock(Lock $request, Forum $forum, Post $post)
    {
        if ($post->locked) {
            $post->locked = false;
        } else {
            $post->locked = true;
        }

        $post->save();

        return response()->json([
            'message' => 'success',
            'post' => new PostResource($post)
        ], 200);
    }
}
