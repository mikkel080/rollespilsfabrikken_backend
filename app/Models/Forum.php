<?php

namespace App\Models;

use App\Models\Obj;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;


/**
 * Class Forum
 * @mixin Builder
 */
class Forum extends Model
{
    public function obj() {
        return $this->belongsTo('App\Models\Obj');
    }

    public function permissions() {
        return $this->obj()->first()->permissions;
    }

    // TODO: POSTS AND COMMENTS
}
