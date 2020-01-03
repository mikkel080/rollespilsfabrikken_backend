<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Event;
use Faker\Generator as Faker;

$factory->define(Event::class, function (Faker $faker) {
    return [
        'user_id' => rand(1, DB::table('users')->select('id')->get()->pluck('id')->count()),
        'calendar_id' => 1,
        'title' => $faker->text,
        'description' => $faker->text(400),
        'start' => $faker->dateTime,
        'end' => $faker->dateTime
    ];
});
