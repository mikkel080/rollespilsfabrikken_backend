<?php

namespace App\Http\Resources\Calendar;

use Illuminate\Http\Resources\Json\JsonResource;

class CalendarWithEvents extends JsonResource
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
            'colour' => $this->colour,
            'events' => $this->events()->count()
        ];
    }
}
