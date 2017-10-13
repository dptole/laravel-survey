<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SurveysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::defaultStringLength(191);
        Schema::create('surveys', function(Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->increments('id')->unsigned();
            $table->unsignedInteger('user_id');
            $table->string('name');
            $table->string('uuid')->unique();
            $table->text('description')->nullable();
            $table->enum('status', ['draft', 'ready'])->default('draft');
            $table->dateTime('start_survey')->nullable();
            $table->dateTime('end_survey')->nullable();
            $table->string('shareable_link')->unique();
            $table->timestamps();
        });

        Schema::table('surveys', function(Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('surveys', function(Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
        Schema::dropIfExists('surveys');
    }
}
