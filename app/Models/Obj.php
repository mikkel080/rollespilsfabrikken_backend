<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


// Models
//use App\Models\Permission;
//use App\Models\Role;
//use App\Models\User;

class Obj extends Model
{
    public function permissions() {
        return $this->hasMany('App\Models\Permission');       
    }

    public function object() {
        switch ($this->type) {
            case 'value':
                return $this->hasOne('App\Models\Forum');
                break;
            
            case 'calendar':
                return $this->hasOne('App\Models\Calendar');
                break;

            default:
                break;
        }
    }
}
