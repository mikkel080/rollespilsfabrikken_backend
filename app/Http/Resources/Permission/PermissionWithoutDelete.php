<?php

namespace App\Http\Resources\Permission;

use App\Http\Resources\Forum\ForumWithoutDelete as ForumResource;
use Illuminate\Http\Resources\Json\JsonResource;

class PermissionWithoutDelete extends JsonResource
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
            'parent' => new ForumResource($this->obj->obj),
            'id' => $this->uuid,
            'level' => $this->level,
            'title' => $this->title,
            'description' => $this->description,
        ];
    }
}
