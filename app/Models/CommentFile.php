<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Class CommentFile
 *
 * @property int $id
 * @property int $comment_id
 * @property int $file_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @mixin Builder
 */
class CommentFile extends Model
{
    public function comment() {
        return $this->belongsTo('App\Models\Comment');
    }

    public function file() {
        return $this->belongsTo('App\Models\File');
    }
}
