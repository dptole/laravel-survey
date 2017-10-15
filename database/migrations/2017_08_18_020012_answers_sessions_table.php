<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AnswersSessionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::defaultStringLength(191);
        Schema::create('answers_sessions', function(Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->increments('id')->unsigned();
            $table->string('session_id');
            $table->unsignedInteger('survey_id');
            $table->text('request_info');
            $table->timestamps();
        });

        Schema::table('answers_sessions', function(Blueprint $table) {
            $table->foreign('survey_id')->references('id')->on('surveys')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('answers_sessions', function(Blueprint $table) {
            $table->dropForeign(['survey_id']);
        });
        Schema::dropIfExists('answers_sessions');
    }
}
