<?php

namespace App\Http\Resources\Post;

use App\Http\Resources\PostFile\PostFile as PostFileResource;
use App\Models\Comment;
use Illuminate\Http\Resources\Json\JsonResource;

class Post extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->uuid,
            'user_id' => $this->user->uuid,
            'title' => $this->title,
            'body' => $this->body,
            'pinned' => $this->pinned,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'files' => $this->when($this->files, PostFileResource::collection($this->files)),
<<<<<<< HEAD
	    'comments' => $this->comments()->count(),
=======
            'permissions' => [
                'can_update' => auth()->user()->can('update', $this->resource),
                'can_delete' => auth()->user()->can('delete', $this->resource),
                'can_add_comments' => auth()->user()->can('create', [Comment::class, $this->resource->forum])
            ],
>>>>>>> 6633f7a54b0aefa09a8901beccfb2c5624cc1f05
        ];
    }
}
