<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class QuestionsOptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::defaultStringLength(191);
        Schema::create('questions_options', function(Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->increments('id')->unsigned();
            $table->unsignedInteger('question_id');
            $table->string('description', 1023);
            $table->enum('type', ['check', 'free'])->default('check');
            $table->string('uuid')->unique();
            $table->timestamps();
        });

        Schema::table('questions_options', function(Blueprint $table) {
            $table->foreign('question_id')->references('id')->on('questions')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('questions_options', function(Blueprint $table) {
            $table->dropForeign(['question_id']);
        });
        Schema::dropIfExists('questions_options');
    }
}
