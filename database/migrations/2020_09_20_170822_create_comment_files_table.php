<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCommentFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('comment_files', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('comment_id')->index();
            $table->unsignedBigInteger('file_id')->index();
            $table->timestamps();

            $table
                ->foreign('comment_id')
                ->references('id')
                ->on('comments')
                ->onDelete('cascade');

            $table
                ->foreign('file_id')
                ->references('id')
                ->on('files')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('comment_files');
    }
}
