<?php

use App\Models\Calendar;
use App\Models\Obj;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use Faker\Generator;

class CalendarProductionSeeder extends Seeder
{
    private Generator $faker;

    private function create($title, $description) {
        (new Calendar())
            ->fill([
                'title' => $title,
                'description' => $description,
                'colour' => $this->faker->hexColor
            ])
            ->obj()
            ->associate((new Obj)->create([
                'type' => 'calendar'
            ]))->save();
    }

    public function run()
    {
        $this->faker = Faker::create();

        self::create('Vanbooking',          'Booking af varevogne');
        self::create('Lokalebooking',       'Booking af lokaler');
        self::create('Faste arrangementer', 'Faste arrangementer');
        self::create('Nøglebærere',         '');
    }
}
