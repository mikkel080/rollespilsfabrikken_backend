<?php

namespace App\Http\Resources\Forum;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Post\PostWithUserCollection;
class ForumWithPosts extends JsonResource
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
            'name' => $this->title,
            'description' => $this->description,
            'colour' => $this->colour,
            'posts' => $this->posts()->count()
        ];
    }
}
