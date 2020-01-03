<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Event
 * @mixin Builder
 */
class Event extends Model
{
    public function calendar() {
        return $this->belongsTo('App\Models\Calendar');
    }
}
