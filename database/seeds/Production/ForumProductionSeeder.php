<?php

use App\Models\Forum;
use App\Models\Obj;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use Faker\Generator;

class ForumProductionSeeder extends Seeder
{
    private Generator $faker;

    private function create($title, $description) {
        (new Forum())
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

        self::create('Rude Skov',           '');
        self::create('Amager Fælled',       '');
        self::create('Den Magiske Skole',   '');
        self::create('Nøglebærere',         '');
        self::create('Andet',               '');
    }
}
