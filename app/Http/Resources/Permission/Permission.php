<?php

namespace App\Http\Resources\Permission;

use App\Http\Resources\Universal\ParentResource;
use Illuminate\Http\Resources\Json\JsonResource;

class Permission extends JsonResource
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
            'parent' => new ParentResource($this->obj->obj),
            'id' => $this->uuid,
            'level' => $this->level,
            'title' => $this->title,
            'description' => $this->description,
        ];
    }
}
