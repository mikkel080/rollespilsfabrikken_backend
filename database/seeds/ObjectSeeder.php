<?php

use Illuminate\Database\Seeder;
use Faker\Generator as Faker;

class ObjectSeeder extends Seeder
{
    private function permTitle($j, $obj) {
        $title = "";
        switch ($j) {
            case 1:
                $title = $obj['title'] . ' - Kan ikke se';
                break;
            case 2:
                $title = $obj['title'] . ' - Kan se';
                break;
            case 3:
                $title = $obj['title'] . ' - Kan kommentere';
                break;
            case 4:
                $title = $obj['title'] . ' - Kan oprette';
                break;
            case 5:
                $title = $obj['title'] . ' - Kan moderere';
                break;
            case 6:
                $title = $obj['title'] . ' - Kan administrere';
                break;
        }

        return $title;
    }
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

            $forum = (new App\Models\Forum)->create([
                'title' => 'forum ' . ($i+1),
                'description' => 'forum number' . ($i+1),
                'obj_id' => $obj['id']
            ]);


            for ($j = 1; $j <= 6; $j++) {
                $title = $this->permTitle($j, $forum);

                (new App\Models\Permission)->create([
                    'obj_id' => $obj['id'],
                    'level' => $j,
                    'title' => $title,
                    'description' => 'description'
                ]);
            }
        }

        for($i = 0; $i != 10; $i++) {
            $obj = (new App\Models\Obj)->create([
                'type' => 'calendar'
            ]);

            $calendar = (new App\Models\Calendar)->create([
                'title' => 'calendar ' . ($i+1),
                'description' => 'calendar number' . ($i+1),
                'obj_id' => $obj['id']
            ]);

            for ($j = 1; $j != 6; $j++) {
                $title = $this->permTitle($j, $calendar);

                (new App\Models\Permission)->create([
                    'obj_id' => $obj['id'],
                    'level' => $j,
                    'title' => $title,
                    'description' => 'description'
                ]);
            }
        }
    }
}
