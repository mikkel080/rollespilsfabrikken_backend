<?php

use Illuminate\Database\Seeder;
use Faker\Generator as Faker;
use Illuminate\Support\Facades\DB;

// Models
use App\Models\Obj;
use App\Models\Forum;
use App\Models\Calendar;

class ObjectsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */

    private function insertPermissions($id) {
        for ($i=0; $i < 6; $i++) { 
            
        }
    }

    private function insert($table, $id) {
        DB::table($table)->insert([
            'title' => $faker->word(),
            'description' => $faker->paragraph($nbSentences = 3, $variableNbSentences = true),
            'object_id' => $id,
        ]);
    }

    private function makeObj($type) {
        return DB::table('obj')->insertGetId([
            'type' => $type
        ]);
    }

    public function run()
    {
        // Forums
        for ($i=0; $i < 100; $i++) { 
            $this->insert('forums', $this->makeObj('forum'));
        }

        // Calendars
        for ($i=0; $i < 100; $i++) { 
            $this->insert('calendars', $this->makeObj('calendar'));
        }
    }
}
