<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Models\Obj;

class Forum extends Model
{
    public function object() {
        return $this->belongsTo('App\Models\Obj');
    }

    public function permissions() {
        $obj = $this->object;

        return $obj->permissions;
    }

    // TODO: POSTS AND COMMENTS
}
