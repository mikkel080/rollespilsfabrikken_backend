<?php

namespace App\Http\Resources\Event;

use App\Http\Resources\User\User as UserResource;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class EventWithUser extends JsonResource
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
            'description' => $this->description,
            'start' => $this->start,
            'end' => $this->end,
            'recurrence' => $this->recurrence,
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at,
        ];
    }
}
