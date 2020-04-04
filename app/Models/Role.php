<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Laravel\Scout\Searchable;

/**
 * Class Role
 * @mixin Builder
 */
class Role extends Model
{
    use Searchable;

    protected $fillable = [
      'title'
    ];

    public function toSearchableArray() {
        $array = $this->toArray();

        $array = Arr::only($array, [
            'id',
            'title',
        ]);

        return $array;
    }

    public function permissions() {
        return $this->hasManyThrough('App\Models\Permission', 'App\Models\RolePerm', 'role_id', 'id', 'id', 'permission_id');
    }
}
