<?php

namespace App\Models;

use Dyrynda\Database\Casts\EfficientUuid;
use Dyrynda\Database\Support\GeneratesUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Post
 * @mixin Builder
 */
class PostFile extends Model
{
    use GeneratesUuid;

    protected $casts = [
        'uuid' => EfficientUuid::class,
    ];

    protected $fillable = [
        'name',
        'saved_name'
    ];

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function post() {
        return $this->belongsTo('App\Models\Post');
    }
}
