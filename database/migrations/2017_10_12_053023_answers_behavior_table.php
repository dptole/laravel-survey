<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AnswersBehaviorTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('answers_behaviors', function(Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->increments('id')->unsigned();
            $table->unsignedInteger('answers_session_id');
            $table->text('behavior');
            $table->timestamps();
        });

        Schema::table('answers_behaviors', function(Blueprint $table) {
            $table->foreign('answers_session_id')->references('id')->on('answers_sessions')->onDelete('cascade');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        Schema::table('answers_behaviors', function(Blueprint $table) {
            $table->dropForeign(['answers_session_id']);
        });
        Schema::dropIfExists('answers_behaviors');
    }
}
