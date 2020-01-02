<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RolePerm extends Model
{
    public function role() {
        return $this->belongsTo('App\Models\Role');
    }

    public function permission() {
        return $this->hasOne('App\Models\Permission');
    }
}
