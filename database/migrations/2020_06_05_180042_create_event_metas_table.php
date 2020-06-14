<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventMetasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('event_metas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id')->index();
            $table->unsignedBigInteger('repeat_start');
            $table->unsignedBigInteger('repeat_interval');
            $table->unsignedBigInteger('repeat_end')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('event_metas');
    }
}
