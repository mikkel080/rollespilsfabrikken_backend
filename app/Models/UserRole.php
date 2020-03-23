<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Class UserRole
 * @mixin Builder
 */
class UserRole extends Model
{
    protected $fillable = [
        'user_id',
        'role_id'
    ];

    public function user() {
        return $this->belongsTo('App\Models\User');
    }

    public function role() {
        return $this->belongsTo('App\Models\Role');
    }
}
