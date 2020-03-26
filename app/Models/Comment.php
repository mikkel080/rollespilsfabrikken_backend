<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Laravel\Scout\Searchable;

/**
 * Class Comment
 * @mixin Builder
 */
class Comment extends Model
{
    use Searchable;
    protected $fillable = [
        'body',
        'parent_id',
        'post_id',
        'user_id'
    ];

    public function toSearchableArray() {
        $array = $this->toArray();

        $array = Arr::only($array, [
            'id',
            'title',
            'body'
        ]);

        return $array;
    }

    public function user() {
        return $this->belongsTo('App\Models\User');
    }

    public function post() {
        return $this->belongsTo('App\Models\Post');
    }

    public function forum() {
        return $this->post->forum;
    }

    public function comments() {
        return $this->hasMany(Comment::class, 'parent_id');
    }

    public function childComments() {
        return $this->hasMany(Comment::class, 'parent_id')->with('childComments');
    }

    public function parent() {
        if ($this['parent_id'] == null) {
            return $this->post();
        }

        return $this->belongsTo('App\Models\Comment', 'parent_id', 'id');
    }

    public function getTableColumns() {
        return $this->getConnection()->getSchemaBuilder()->getColumnListing($this->getTable());
    }
}
