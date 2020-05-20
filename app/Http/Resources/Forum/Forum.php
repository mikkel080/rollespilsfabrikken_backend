<?php

namespace App\Http\Resources\Forum;

use App\Models\Post;
use App\Models\Comment;
use Illuminate\Http\Resources\Json\JsonResource;

class Forum extends JsonResource
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
            'permissions' => [
                'can_update' => auth()->user()->can('update', $this->resource),
                'can_delete' => auth()->user()->can('delete', $this->resource),
                'can_add_posts' => auth()->user()->can('create',[Post::class, $this->resource]),
                'can_add_comments' => auth()->user()->can('create',[Comment::class, $this->resource]),
            ]
        ];
    }
}
