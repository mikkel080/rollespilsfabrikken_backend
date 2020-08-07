<?php

namespace App\Models;

use Dyrynda\Database\Casts\EfficientUuid;
use Dyrynda\Database\Support\GeneratesUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Class PostFile
 *
 * @property int $id
 * @property string $uuid
 * @property int $post_id
 * @property string $name
 * @property string $saved_name
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
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
        'saved_name',
        'file_size'
    ];

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function post() {
        return $this->belongsTo('App\Models\Post');
    }
}
