<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Laravel\Scout\Searchable;

/**
 * Class Post
 * @mixin Builder
 */
class Post extends Model
{
    use Searchable;
    protected $fillable = [
        'title',
        'body'
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

    public function forum() {
        return $this->belongsTo('App\Models\Forum');
    }

    public function user() {
        return $this->belongsTo('App\Models\User');
    }

    public function comments() {
        return $this->hasMany('App\Models\Comment');
    }

    public function getThreadedComments() {
        return $this->comments()->with('user')->get()->threaded();
    }

    public function getTableColumns() {
        return $this->getConnection()->getSchemaBuilder()->getColumnListing($this->getTable());
    }
}
