<?php

namespace App\Http\Resources\Comment;

use App\Http\Resources\Comment\CommentWithChildComments as CommentResource;
use App\Http\Resources\User\User as UserResource;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentWithChildComments extends JsonResource
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
            'id' => $this->id,
            'parent_id' => $this->parent_id,
            'user' => new UserResource((new User)->find($this->user_id)),
            'body' => $this->body,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'child_comments' => CommentResource::collection($this->comments)
        ];
    }
}
