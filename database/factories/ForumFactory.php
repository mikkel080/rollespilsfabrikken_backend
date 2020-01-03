<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Forum;
use Faker\Generator as Faker;


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
    for ($j = 1; $j <= 6; $j++) {
        $title = "";
        switch ($j) {
            case 1:
                $title = $forum['title'] . ' - Kan ikke se';
                break;
            case 2:
                $title = $forum['title'] . ' - Kan se';
                break;
            case 3:
                $title = $forum['title'] . ' - Kan kommentere';
                break;
            case 4:
                $title = $forum['title'] . ' - Kan oprette';
                break;
            case 5:
                $title = $forum['title'] . ' - Kan moderere';
                break;
            case 6:
                $title = $forum['title'] . ' - Kan administrere';
                break;
        }

        (new App\Models\Permission)->create([
            'obj_id' => $forum['obj_id'],
            'level' => $j,
            'title' => $title,
            'description' => $faker->text
        ]);
    }

    factory(App\Models\Post::class, 20)->create([
        'forum_id' => $forum['id']
    ]);
});
