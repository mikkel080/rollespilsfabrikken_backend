<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Class EventResource
 *
 * @property int $id
 * @property integer $event_id
 * @property integer $resource_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @mixin Builder
 */
class EventResource extends Model
{
    public function event() {
        return $this->belongsTo('App\Models\Event');
    }

    public function resource() {
        return $this->belongsTo('App\Models\Resource');
    }
}
