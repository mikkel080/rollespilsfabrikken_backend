<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Comment
 * @mixin Builder
 */
class Comment extends Model
{
    public function user() {
        return $this->belongsTo('App\Models\User');
    }

    public function post() {
        return $this->belongsTo('App\Models\Post');
    }

    public function forum() {
        return $this->post->forum;
    }

    public function parent() {
        if ($this['parent_id'] == null) {
            return $this->post;
        }

        return $this->belongsTo('App\Models\Comment', 'parent_id', 'id');
    }

    public function comments() {
        return $this->hasMany('App\Models\Comment', 'parent_id', 'id');
    }
}
