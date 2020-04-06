<?php

/** @var Factory $factory */

use App\Models\Forum;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;


$factory->define(Forum::class, function (Faker $faker) {
    return [
        'title' => $faker->streetName,
        'description' => $faker->text(200),
        'obj_id' => function() {
            return (new App\Models\Obj)->create([
                'type' => 'forum'
            ])['id'];
        }
    ];
});

$factory->afterCreating(App\Models\Forum::class, function ($forum, $faker) {
    factory(App\Models\Post::class, 1)->create([
        'forum_id' => $forum['id']
    ]);
});
