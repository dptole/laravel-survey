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
        Schema::create('questions_options', function(Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->increments('id')->unsigned();
            $table->unsignedInteger('question_id');
            $table->string('description', 1023);
            $table->enum('type', ['check', 'free'])->default('check');
            $table->string('uuid')->unique();
            $table->integer('version')->default('1');
            $table->timestamps();
        });

        Schema::table('questions_options', function(Blueprint $table) {
            $table->foreign('question_id')->references('id')->on('questions')->onDelete('cascade');
        });

        DB::statement('CREATE VIEW questions_options_meta AS SELECT question_id, MAX(version) AS last_version FROM questions_options GROUP BY question_id;');
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

        DB::statement('DROP VIEW questions_options_meta;');
    }
}
