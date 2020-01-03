<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Class RolePerm
 * @mixin Builder
 */
class RolePerm extends Model
{
    public function role() {
        return $this->belongsTo('App\Models\Role');
    }

    public function permission() {
        return $this->belongsTo('App\Models\Permission');
    }
}
