<?php

namespace App\Models;

use App\Models\Obj;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Calendar
 * @mixin Builder
 */
class Calendar extends Model
{

    public function obj() {
        return $this->belongsTo('App\Models\Obj');
    }

    public function permissions() {
        return $this->obj()->first()->permissions;
    }
/*
    public function events() {
        return $this->hasMany('App\Models\Event');
    }*/
}
