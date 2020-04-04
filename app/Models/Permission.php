<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Laravel\Scout\Searchable;

/**
 * Class Permission
 * @mixin Builder
 */
class Permission extends Model
{
    use Searchable;

    protected $fillable = [
        'obj_id',
        'level',
        'title',
        'description'
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

    public function getTableColumns() {
        return $this->getConnection()->getSchemaBuilder()->getColumnListing($this->getTable());
    }

    public function obj() {
        return $this->belongsTo('App\Models\Obj');
    }

    public function roles() {
        return $this->belongsToMany('App\Models\Role', 'role_perms');
    }
}
