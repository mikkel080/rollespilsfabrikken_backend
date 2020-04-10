<?php

namespace App\Http\Resources\SecurityQuestion;

use Illuminate\Http\Resources\Json\JsonResource;

class SecurityQuestion extends JsonResource
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
          'question' => $this->question,
          'answer' => $this->answer
        ];
    }
}
