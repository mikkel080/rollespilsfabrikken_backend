<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    public function calendar() {
        return $this->belongsTo('App\Models\Calendar');
    }
}
