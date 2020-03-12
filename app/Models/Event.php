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
    protected $fillable = [
        'calendar_id',
        'user_id',
        'title',
        'description',
        'start',
        'end'
    ];

    public function calendar() {
        return $this->belongsTo('App\Models\Calendar');
    }
}
