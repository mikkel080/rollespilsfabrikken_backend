<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    public function object() {
        return $this->belongsTo('App\Models\Object');
    }

    public function roles() {
        return $this->belongsTo('App\Models\Role', 'App\Models\RolePerm', 'permission_id', 'id', 'id', 'role_id');
    }
}
