<?php

namespace App\Http\Resources\CommentFile;

use Illuminate\Http\Resources\Json\JsonResource;

class CommentFile extends JsonResource
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
