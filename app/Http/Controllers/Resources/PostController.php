<?php

namespace App\Http\Controllers\Resources;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Helpers;
use App\Http\Requests\API\Post\Destroy;
use App\Http\Requests\API\Post\Index;
use App\Http\Requests\API\Post\Newest;
use App\Http\Requests\API\Post\Pin;
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

class PostController extends Controller
{

    private function saveFile(UploadedFile $file, Post $post) {
        $fileContent = $file->get();
        $encryptedContent = encrypt($fileContent);

        $postFile = (new PostFile)->fill([
            'name' => $file->getClientOriginalName(),
            'saved_name' => 'tmp'
        ]);

        $post->files()->save($postFile);

        $name = $postFile->refresh()->uuid . '.dat';
        Storage::put($name, $encryptedContent);

        $postFile->saved_name = $name;
        $postFile->save();

        return $postFile;
    }

    /**
     * Display a listing of the resource.
     * Url : /api/forum/{forum}/posts
     *
     * @param Newest $request
     * @param Forum $forum
     * @return JsonResponse
     */
    public function newest(Newest $request,  Forum $forum)
    {
        $user = auth()->user();

        $forums = Forum::query();

        if (!$user->isSuperUser()) {
            $forums
                ->whereIn('obj_id',
                    collect($user->permissions())
                        ->where('level', '>', 1)
                        ->pluck('obj_id')
                );
        }

        $forums
            ->select('id')
            ->get();

        $query = Post::query()
            ->whereIn('forum_id', $forums)
            ->orderBy('created_at', 'desc');

        return response()->json([
            'message' => 'success',
            'data' => new PostIndexNewestCollection((new Helpers())->filterItems($request, $query)),
        ], 200);
    }

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
        if ($request->query('search')) {
            $posts = (new Helpers())->searchItems($request, Post::class, [
                [
                    'key' => 'forum_id',
                    'value' => $forum['id']
                ]
            ]);
        } else {
            $query = $forum
                ->posts()
                ->orderBy('pinned', 'desc')
                ->getQuery();

            $posts = (new Helpers())->filterItems($request, $query);
        }

        return response()->json([
            'message' => 'success',
            'data' => new PostIndexCollection($posts),
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
            'post' => new PostWithUserResource($post),
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     * Url : /api/forum/{forum}/post/
     *
     * @param Store $request
     * @param Forum $forum
     * @return JsonResponse
     * @throws FileNotFoundException
     */
    public function store(Store $request, Forum $forum)
    {
        $post = new Post();
        $post
            ->fill($request->validated())
            ->user()
            ->associate(auth()->user());

        $forum->posts()->save($post);

        if ($request->hasFile(('files'))) {
            foreach ($request->file('files') as $file) {
                self::saveFile($file, $post);
            }
        }

        return response()->json( [
            'message' => 'success',
            'post' => new PostResource($post->refresh())
        ], 201);
    }

    /**
     * Update the specified resource in storage.
     * Url : /api/forum/{forum}/post/{post}
     *
     * @param Update $request
     * @param Forum $forum
     * @param Post $post
     * @return JsonResponse
     */
    public function update(Update $request, Forum $forum, Post $post)
    {
        $post->update($request->validated());

        return response()->json([
            'message' => 'success',
            'post' => new PostResource($post)
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     * Url : /api/forum/{forum}/post/{post}
     *
     * @param Destroy $request
     * @param Forum $forum
     * @param Post $post
     * @return JsonResponse
     */
    public function destroy(Destroy $request, Forum $forum, Post $post)
    {
        $post->delete();

        return response()->json([
           'message' => "success"
        ], 200);
    }

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

    public function file(AddFile $request, Forum $forum, Post $post) {
        if ($request->hasFile(('files'))) {
            foreach ($request->file('files') as $file) {
                $existingFile = (new PostFile)->where([
                    ['post_id', '=', $post['id']],
                    ['name', '=', $file->getClientOriginalName()]
                ])->first();

                if ($existingFile) {
                    Storage::delete($existingFile->saved_name);
                    $existingFile->delete();
                }

                self::saveFile($file, $post);
            }
        }

        if ($request->exists('file_changes')) {
            foreach ($request->validated()['file_changes'] as $file_change) {
                $file = (new PostFile)->whereUuid($file_change['id'])->first();

                if (!$file) {
                    continue;
                }

                if ($file_change['change'] === 'delete') {
                    Storage::delete($file->saved_name);
                    $file->delete();
                }
            }
        }

        return response()->json([
            'message' => 'success',
            'post' => new PostResource($post)
        ], 200);
    }

    public function getFile(DownloadFile $request, Forum $forum, Post $post, PostFile $file) {
        try {
            $contents = Storage::get($file->saved_name);
        } catch (FileNotFoundException $e) {
            return response()->json([
                'message' => 'Could not find file, it might have been deleted'
            ], 404);
        } catch (\Exception $e) {
            Log::error($e);

            return response()->json([
                'message' => 'Something went wrong. Check with the administrator'
            ], 500);
        }

        $decrypted = decrypt($contents);

        return response()->make($decrypted, 200, array(
            'Content-Type' => (new finfo(FILEINFO_MIME))->buffer($decrypted),
            'Content-Disposition' => 'attachment; filename="' . pathinfo($file->name, PATHINFO_BASENAME) . '"'
        ));
    }
}
