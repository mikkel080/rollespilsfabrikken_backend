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

class PostFileController extends Controller
{
    public function file(AddFile $request, Forum $forum, Post $post) {
        if ($request->hasFile(('files'))) {
            foreach ($request->file('files') as $file) {
                $existingFile = (new PostFile)->where([
                    ['post_id', '=', $post['id']],
                    ['name', '=', $file->getClientOriginalName()]
                ])->first();

                if ($existingFile) {
                    Storage::delete('post_uploads\\' .$existingFile->saved_name);
                    $existingFile->delete();
                }

                FileHelpers::saveFile($file, $post);
            }
        }

        if ($request->exists('file_deletions')) {
            foreach ($request->validated()['file_deletions'] as $fileDeletion) {
                $file = (new PostFile)->whereUuid($fileDeletion)->first();

                if (!$file) {
                    continue;
                }

                Storage::delete('post_uploads\\' .$file->saved_name);
                $file->delete();
            }
        }

        return response()->json([
            'message' => 'success',
            'post' => new PostResource($post)
        ], 200);
    }

    public function getFile(DownloadFile $request, Forum $forum, Post $post, PostFile $file) {
        try {
            $contents = Storage::get('post_uploads\\' . $file->saved_name);
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
