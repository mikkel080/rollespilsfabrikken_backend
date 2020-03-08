<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

/**
 * Class User
 * @mixin Builder
 */
class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username',
        'email',
        'password',
        'active',
        'activation_token',
        'avatar',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'activation_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected $appends = [
        'avatar_url',
    ];

    public function getAvatarUrlAttribute() {
        return asset('storage/avatars/' . $this->id . '/' . $this->avatar);
    }

    public function roles() {
        return $this->hasManyThrough('App\Models\Role', 'App\Models\UserRole', 'user_id', 'id', 'id', 'role_id');
    }

    public function permissions() {
        $roles = $this->roles()->get();

        $perms = collect();
        foreach ($roles as $role) {
            $perm = $role->permissions()->get();
            $perms = $perms->merge($perm);
            $perms = $perms->unique('id');
        }
        $perms = collect($perms);

        return $perms->unique()->values()->all();
    }

    /* public function info() {
        return $this->hasOne('App\Model\UserInfo');
    } */

    public function posts() {
        return $this->hasMany('App\Models\Post');
    }

    public function comments() {
        return $this->hasMany('App\Models\Comment');
    }

    public function events() {
        return $this->hasMany('App\Models\Event');
    }

}
