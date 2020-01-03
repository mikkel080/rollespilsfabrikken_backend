<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    public function forum() {
        return $this->belongsTo('App\Models\Forum');
    }

    public function user() {
        return $this->belongsTo('App\Models\User');
    }

    public function comments() {
        return $this->hasMany('App\Models\Comments')->select()->where('parent_id', '=', null);
    }
}
