<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSecurityQuestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('security_questions', function (Blueprint $table) {
            $table->id();
            $table->efficientUuid('uuid')->index();
            $table->string('question');
            $table->string('answer');
            $table->timestamp('last_answered_at')->nullable();
            $table->timestamps();
        });

        Artisan::call('db:seed', [
            '--class' => SecurityQuestionProductionSeeder::class,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('security_questions');
    }
}
