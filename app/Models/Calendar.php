<?php

namespace App\Models;

use App\Models\Obj;

use Illuminate\Database\Eloquent\Model;

class Calendar extends Model
{
    public function object() {
        return $this->belongsTo('App\Models\Obj');
    }

    public function permissions() {
        $obj = $this->object;

        return $obj->permissions;
    }
}
