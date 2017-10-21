<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class QuestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('questions', function(Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->increments('id')->unsigned();
            $table->unsignedInteger('survey_id');
            $table->unsignedInteger('order')->default('1');
            $table->unsignedInteger('version')->default('1');
            $table->string('description', 1023);
            $table->string('uuid')->unique();
            $table->enum('active', ['1', '0'])->default('1');
            $table->timestamps();
        });

        Schema::table('questions', function(Blueprint $table) {
            $table->foreign('survey_id')->references('id')->on('surveys')->onDelete('cascade');
        });

        DB::statement('CREATE VIEW surveys_last_version_view AS SELECT survey_id, MAX(version) AS last_version FROM questions WHERE active = 1 GROUP BY survey_id;');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('questions', function(Blueprint $table) {
            $table->dropForeign(['survey_id']);
        });
        Schema::dropIfExists('questions');

        DB::statement('DROP VIEW surveys_last_version_view;');
    }
}

