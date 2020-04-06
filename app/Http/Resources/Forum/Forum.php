<?php

namespace App\Http\Resources\Forum;

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
            'title' => $this->title,
            'description' => $this->description,
        ];
    }
}
