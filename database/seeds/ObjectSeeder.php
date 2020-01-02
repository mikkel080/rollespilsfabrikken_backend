<?php

use Illuminate\Database\Seeder;
use Faker\Generator as Faker;

class ObjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for($i = 0; $i != 10; $i++) {
            $obj = (new App\Models\Obj)->create([
                'type' => 'forum'
            ]);

            (new App\Models\Forum)->create([
                'title' => 'forum ' . ($i+1),
                'description' => 'forum number' . ($i+1),
                'object_id' => $obj['id']
            ]);
        }

        for($i = 0; $i != 10; $i++) {
            $obj = (new App\Models\Obj)->create([
                'type' => 'calendar'
            ]);

            App\Models\Calendar::create([
                'title' => 'forum ' . ($i+1),
                'description' => 'forum number' . ($i+1),
                'object_id' => $obj['id']
            ]);
        }
    }
}
