<?php

namespace App\Http\Resources\Post;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\User\User as UserResource;
use App\Models\User;

class PostIndex extends JsonResource
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
            'pinned' => $this->pinned,
            'has_files' => $this->files->count() > 0,
            'comments' => $this->comments()->count(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

        ];
    }
}
