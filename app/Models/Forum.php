<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Laravel\Scout\Searchable;

/**
 * Class Forum
 * @mixin Builder
 */
class Forum extends Model
{
    use Searchable;
    protected $fillable = [
        'title',
        'description',
        'obj_id'
    ];

    public function obj() {
        return $this->belongsTo('App\Models\Obj');
    }

    public function toSearchableArray() {
        $array = $this->toArray();

        $array = Arr::only($array, [
            'id',
            'title',
            'description'
        ]);

        return $array;
    }

    public function permissions() {
        return $this->obj()->first()->permissions;
    }

    public function posts() {
        return $this->hasMany('App\Models\Post');
    }

    public function comments() {
        return $this->hasManyThrough('App\Models\Comment', 'App\Models\Post');
    }

    public function getTableColumns() {
        return $this->getConnection()->getSchemaBuilder()->getColumnListing($this->getTable());
    }
}
