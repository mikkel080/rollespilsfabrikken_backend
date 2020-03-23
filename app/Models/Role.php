<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Role
 * @mixin Builder
 */
class Role extends Model
{
    protected $fillable = [
      'title'
    ];

    public function permissions() {
        return $this->hasManyThrough('App\Models\Permission', 'App\Models\RolePerm', 'role_id', 'id', 'id', 'permission_id');
    }
}
