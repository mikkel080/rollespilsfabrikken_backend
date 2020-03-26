<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Laravel\Scout\Searchable;

/**
 * Class Calendar
 * @mixin Builder
 */
class Calendar extends Model
{
    use Searchable;
    protected $fillable = [
        'title',
        'description',
        'obj_id'
    ];

    public function toSearchableArray() {
        $array = $this->toArray();

        $array = Arr::only($array, [
            'id',
            'title',
            'description'
        ]);

        return $array;
    }

    public function obj() {
        return $this->belongsTo('App\Models\Obj');
    }

    public function permissions() {
        return $this->obj()->first()->permissions;
    }

    public function events() {
        return $this->hasMany('App\Models\Event');
    }

    public function getTableColumns() {
        return $this->getConnection()->getSchemaBuilder()->getColumnListing($this->getTable());
    }
}
