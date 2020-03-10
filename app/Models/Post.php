<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Post
 * @mixin Builder
 */
class Post extends Model
{
    protected $fillable = [
        'user_id',
        'forum_id',
        'title',
        'body'
    ];

    public function forum() {
        return $this->belongsTo('App\Models\Forum');
    }

    public function user() {
        return $this->belongsTo('App\Models\User');
    }

    public function comments() {
        return $this->hasMany('App\Models\Comment')->select()->where('parent_id', '=', null);
    }
}
