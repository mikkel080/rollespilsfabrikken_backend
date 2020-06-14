<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Event
 * @mixin Builder
 */
class EventMeta extends Model
{
    protected $fillable = [
        'repeat_start',
        'repeat_interval',
        'repeat_end',
    ];

    public function event() {
        return $this->belongsTo(Event::class);
    }
}
