<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Role;

use Faker\Factory as Faker;

class AddColorToRoles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->string('color');
        });

        $existing_roles = Role::where('color', '=', '')->get();
        $faker = Faker::create();

        foreach ($existing_roles as $role) {
            $role->color = $faker->hexColor;
            $role->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn(['color']);
        });
    }
}
