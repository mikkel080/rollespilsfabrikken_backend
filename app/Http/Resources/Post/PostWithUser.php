<?php

namespace App\Http\Resources\Post;

use App\Http\Resources\PostFile\PostFile as PostFileResource;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\User\User as UserResource;
use App\Models\User;

class PostWithUser extends JsonResource
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
            'user' => new UserResource((new User)->find($this->user_id)),
            'title' => $this->title,
            'body' => $this->body,
            'pinned' => $this->pinned,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'files' => $this->when($this->files, PostFileResource::collection($this->files)),
        ];
    }
}
