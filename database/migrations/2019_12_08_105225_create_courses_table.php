<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCoursesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->increments('id');
            $table->string('course_code')->unique();
            $table->string('course_title')->unique();
            $table->unsignedInteger('semester')->nullable();
            $table->unsignedInteger('program_id')->nullable();
            $table->unsignedInteger('department_id')->nullable();
            $table->boolean('status')->default(1);
            $table->timestamps();            
        });

        Schema::table('courses', function(Blueprint $table) {
            $table->foreign('program_id')->references('id')->on('programs')->onDelete('cascade');            
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('answers', function(Blueprint $table) {
            $table->dropForeign(['program_id']);
            $table->dropForeign(['department_id']);
        });
        Schema::dropIfExists('courses');
    }
}
