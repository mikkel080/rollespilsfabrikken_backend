<?php

namespace App\Models;

use Dyrynda\Database\Casts\EfficientUuid;
use Dyrynda\Database\Support\GeneratesUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Forum
 * @mixin Builder
 */
class SecurityQuestion extends Model
{
    use GeneratesUuid;

    protected $casts = [
        'uuid' => EfficientUuid::class,
    ];

    protected $fillable = [
        'question',
        'answer',
    ];

    public function getRouteKeyName()
    {
        return 'uuid';
    }
}
