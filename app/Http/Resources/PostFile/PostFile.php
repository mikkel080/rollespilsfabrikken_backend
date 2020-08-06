<?php

namespace App\Http\Resources\PostFile;

use Illuminate\Http\Resources\Json\JsonResource;

class PostFile extends JsonResource
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
            'name' => $this->name,
            'size' => $this->file_size,
        ];
    }
}
