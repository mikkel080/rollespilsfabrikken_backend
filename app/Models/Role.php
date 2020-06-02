<?php

namespace App\Models;

use Dyrynda\Database\Casts\EfficientUuid;
use Dyrynda\Database\Support\GeneratesUuid;
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
    use Searchable, GeneratesUuid;

    protected $casts = [
        'uuid' => EfficientUuid::class,
    ];

    protected $fillable = [
        'title',
        'color',
    ];

    public function getRouteKeyName()
    {
        return 'uuid';
    }

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
