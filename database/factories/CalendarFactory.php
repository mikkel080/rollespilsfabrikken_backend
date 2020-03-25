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
    for ($j = 1; $j <= 6; $j++) {
        $title = "";
        switch ($j) {
            case 1:
                $title = $calendar['title'] . ' - Kan ikke se';
                break;
            case 2:
                $title = $calendar['title'] . ' - Kan se';
                break;
            case 3:
                $title = $calendar['title'] . ' - Kan kommentere';
                break;
            case 4:
                $title = $calendar['title'] . ' - Kan oprette';
                break;
            case 5:
                $title = $calendar['title'] . ' - Kan moderere';
                break;
            case 6:
                $title = $calendar['title'] . ' - Kan administrere';
                break;
        }

        (new App\Models\Permission)->create([
            'obj_id' => $calendar['obj_id'],
            'level' => $j,
            'title' => $title,
            'description' => $faker->text
        ]);
    }

    factory(App\Models\Event::class, 1)->create([
       'calendar_id' => $calendar['id']
    ]);
});
