<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Class User
 * @mixin Builder
 */
class PasswordReset extends Model
{
    protected $fillable = [
        'email', 'token'
    ];

}
