<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Class EventResource
 *
 * @property integer $event_id
 * @property integer $resource_id
 *
 * @mixin Builder
 */
class EventResource extends Model
{
    protected $fillable = [
        'event_id',
        'resource_id'
    ];

    public function event() {
        return $this->belongsTo('App\Models\Event');
    }

    public function resource() {
        return $this->belongsTo('App\Models\Resource');
    }
}
