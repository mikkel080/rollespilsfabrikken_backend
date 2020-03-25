<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Post;
use Faker\Generator as Faker;
use Illuminate\Support\Facades\DB;

$factory->define(Post::class, function (Faker $faker) {
    return [
        'user_id' => rand(1, DB::table('users')->select('id')->get()->pluck('id')->count()),
        'forum_id' => 1,
        'title' => $faker->text,
        'body' => $faker->text(400)
    ];
});

$factory->afterCreating(App\Models\Post::class, function ($post, $factory) {
    factory(App\Models\Comment::class, 1)->create([
        'post_id' => $post['id']
    ]);
});
