<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    public function user() {
        return $this->belongsTo('App\Models\User');
    }

    public function post() {
        return $this->belongsTo('App\Models\Post');
    }

    public function parent() {
        if ($this['parent_id'] == null) {
            return $this->post;
        }

        return $this->belongsTo('App\Models\Comment', 'id', 'parent_id');
    }
}
