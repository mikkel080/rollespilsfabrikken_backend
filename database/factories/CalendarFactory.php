<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Calendar;
use Faker\Generator as Faker;

$factory->define(Calendar::class, function (Faker $faker) {
    return [
        'title' => $faker->streetName,
        'description' => $faker->text(200),
        'obj_id' => function() {
            return (new App\Models\Obj)->create([
                'type' => 'calendar'
            ])['id'];
        }
    ];
});

$factory->afterCreating(App\Models\Calendar::class, function ($calendar, $faker) {
    factory(App\Models\Event::class, 1)->create([
       'calendar_id' => $calendar['id']
    ]);
});
