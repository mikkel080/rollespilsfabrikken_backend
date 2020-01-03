<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Comment;
use Faker\Generator as Faker;
use Illuminate\Support\Facades\DB;

$factory->define(Comment::class, function (Faker $faker) {
    return [
        'user_id' => rand(1, DB::table('users')->select('id')->get()->pluck('id')->count()),
        'post_id' => 1,
        'parent_id' => null,
        'body' => $faker->text
    ];
});
