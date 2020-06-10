<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Event
 * @mixin Builder
 */
class EventSerie extends Model
{
    public function events() {
        return $this->hasMany('App\Models\Event', 'series_id');
    }
}
