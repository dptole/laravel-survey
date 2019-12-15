<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', ['uses' => 'HomeController@root', 'as' => 'root']);


//Changed the following route and removed laravel prefix and google captcha
//Route::group(['prefix' => 'laravel', 'middleware' => ['google_recaptcha', 'email_checkdnsrr']], function() {
Route::group(['middleware' => ['email_checkdnsrr']], function() {
    
    Route::get('/', ['uses' => 'HomeController@index', 'as' => 'home']);
    Route::get('/s/{s_link}', ['uses' => 'PublicSurveyController@shareableLink', 'as' => 'public_survey.shareable_link']);
    Auth::routes();

    Route::group(['prefix' => 'fonts'], function() {
        Route::get('/{font_file}', ['uses' => 'ResourceController@fonts', 'as' => 'fonts']);
    });

    Route::group(['prefix' => 'images/jpg'], function() {
        Route::get('/{image_file}', ['uses' => 'ResourceController@jpgImages', 'as' => 'jpgImages']);
    });

    Route::group(['prefix' => 'r'], function() {
        Route::get('/js.js', ['uses' => 'ResourceController@js', 'as' => 'js']);
        Route::get('/questions.js', ['uses' => 'ResourceController@questions', 'as' => 'questions']);
        Route::get('/start-survey.js', ['uses' => 'ResourceController@startSurvey', 'as' => 'start-survey']);
        Route::get('/manage-survey.js', ['uses' => 'ResourceController@manageSurvey', 'as' => 'manage-survey']);
        Route::get('/stats.js', ['uses' => 'ResourceController@stats', 'as' => 'stats']);
        Route::get('/css.css', ['uses' => 'ResourceController@css', 'as' => 'css']);
    });

    Route::group(['prefix' => 'survey'], function() {
        Route::get('/{uuid}', ['uses' => 'PublicSurveyController@show', 'as' => 'public_survey.show']);
    });

    Route::group(['prefix' => 'api', 'middleware' => 'api'], function() {
        Route::post('/{s_uuid}/session_id', ['uses' => 'APIController@getSessionId']);
        Route::post('/save_answer', ['uses' => 'APIController@saveSurveyAnswer']);
        Route::post('/fetch_country_info', ['uses' => 'APIController@fetchCountryInfo']);
    });


    //Dashboard Routes
    Route::group(['middleware' => 'auth', 'prefix' => 'dashboard'], function() {
        
        Route::get('/', ['uses' => 'DashboardController@getDashboard', 'as' => 'dashboard']);

        //Department Routes
        Route::get('/department', ['uses' => 'DepartmentController@index'])->name('department.index');
        Route::post('/department/store', ['uses' => 'DepartmentController@store'])->name('department.store');
        Route::delete('/department/{id}/destroy', ['uses' => 'DepartmentController@destroy'])->name('department.destroy');

        //Teacher Routes
        Route::get('/teacher', ['uses' => 'TeacherController@index'])->name('teacher.index');
        Route::post('/teacher/store', ['uses' => 'TeacherController@store'])->name('teacher.store');
        Route::get('teacher/{id}/edit', ['uses' => 'TeacherController@edit'])->name('teacher.edit');
        Route::post('/teacher/{id}/update', ['uses' => 'TeacherController@update'])->name('teacher.update');
        Route::delete('/teacher/{id}/destroy', ['uses' => 'TeacherController@destroy'])->name('teacher.destroy');

        //Program Routes
        Route::get('/program', ['uses' => 'ProgramController@index'])->name('program.index');
        Route::post('/program/store', ['uses' => 'ProgramController@store'])->name('program.store');
        Route::get('program/{id}/edit', ['uses' => 'ProgramController@edit'])->name('program.edit');
        Route::post('/program/{id}/update', ['uses' => 'ProgramController@update'])->name('program.update');
        Route::delete('/program/{id}/destroy', ['uses' => 'ProgramController@destroy'])->name('program.destroy');

        //Course Routes
        Route::get('/course', ['uses' => 'CourseController@index'])->name('course.index');
        Route::post('/course/store', ['uses' => 'CourseController@store'])->name('course.store');
        Route::get('course/{id}/edit', ['uses' => 'CourseController@edit'])->name('course.edit');
        Route::post('/course/{id}/update', ['uses' => 'CourseController@update'])->name('course.update');
        Route::delete('/course/{id}/destroy', ['uses' => 'CourseController@destroy'])->name('course.destroy');


        //Survey Routes
        Route::group(['prefix' => 'survey'], function() {
            Route::get('/create', ['uses' => 'SurveyController@create', 'as' => 'survey.create']);
            Route::post('/create', ['uses' => 'SurveyController@store', 'as' => 'survey.store']);

            Route::get('/{s_uuid}/stats', ['uses' => 'SurveyController@stats', 'as' => 'survey.stats']);
            Route::get('/{s_uuid}/delete', ['uses' => 'SurveyController@destroy', 'as' => 'survey.destroy']);

            Route::get('/{s_uuid}/edit', ['uses' => 'SurveyController@edit', 'as' => 'survey.edit']);
            Route::post('/{s_uuid}/edit', ['uses' => 'SurveyController@update', 'as' => 'survey.update']);

            Route::get('/{s_uuid}/run', ['uses' => 'SurveyController@run', 'as' => 'survey.run']);
            Route::get('/{s_uuid}/pause', ['uses' => 'SurveyController@pause', 'as' => 'survey.pause']);

            Route::get('/{s_uuid}/question/create', ['uses' => 'QuestionController@create', 'as' => 'question.create']);
            Route::post('/{s_uuid}/question/create', ['uses' => 'QuestionController@store', 'as' => 'question.store']);

            Route::get('/{s_uuid}/question/{q_uuid}/delete', ['uses' => 'QuestionController@delete', 'as' => 'question.delete']);

            Route::get('/{s_uuid}/question/{q_uuid}/edit', ['uses' => 'QuestionController@edit', 'as' => 'question.edit']);
            Route::post('/{s_uuid}/question/{q_uuid}/edit', ['uses' => 'QuestionController@update', 'as' => 'question.update']);

            Route::get('/{s_uuid}/change_order', ['uses' => 'QuestionController@showChangeOrder', 'as' => 'question.show_change_order']);
            Route::post('/{s_uuid}/change_order', ['uses' => 'QuestionController@storeChangeOrder', 'as' => 'question.store_change_order']);
        });
    });

});

