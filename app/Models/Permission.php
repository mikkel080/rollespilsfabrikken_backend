<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Permission
 * @mixin Builder
 */
class Permission extends Model
{
    protected $fillable = [
        'obj_id',
        'level',
        'title',
        'description'
    ];

    public function object() {
        return $this->belongsTo('App\Models\Object');
    }

    public function roles() {
        return $this->belongsTo('App\Models\Role', 'App\Models\RolePerm', 'permission_id', 'id', 'id', 'role_id');
    }
}
