<?php

namespace App\Http\Resources\Event;

use Illuminate\Http\Resources\Json\JsonResource;

class Event extends JsonResource
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
            'user_id' => $this->user->uuid,
            'title' => $this->title,
            'description' => $this->description,
            'start' => $this->start,
            'end' => $this->end,
            'recurrence' => $this->recurrence,
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at,
            'permissions' => [
                'can_update' => auth()->user()->can('update', $this->resource),
                'can_delete' => auth()->user()->can('delete', $this->resource)
            ],
        ];
    }
}
