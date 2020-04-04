<?php

namespace App\Http\Resources\Permission;

use App\Http\Resources\Forum\Forum as ForumResource;
use App\Http\Resources\Role\Role as RoleResource;
use Illuminate\Http\Resources\Json\JsonResource;

class PermissionWithRoles extends JsonResource
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
            'id' => $this->id,
            'level' => $this->level,
            'title' => $this->title,
            'description' => $this->description,
            'roles' => RoleResource::collection($this->roles),
        ];
    }
}
